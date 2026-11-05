# Glossary — Ubiquitous Language

The shared vocabulary of the domain. Every term here has **one** meaning, used
identically in conversation, tests and code. If the code and this document ever
disagree, one of them is wrong — fix it.

## Phase 1 — Build a ship

| Term | Kind | Definition |
|---|---|---|
| **Ship** | Aggregate Root | A physical ship: a concrete, ownable, storable unit. Has its own **identity** (`ShipId`), independent of its name. Comes into existence through the `Ship::build(id, class, name?)` named constructor, which enforces its invariants. |
| **ShipId** | Value Object | The ship's synthetic identity: a UUIDv7, generated in the domain before persistence. Globally unique and stable — never derived from business data (which can collide or change). |
| **ShipName** | Value Object | The ship's name. Always valid: **non-empty**, length-bounded. **Not unique** — like real vessels, two ships may share a name. **Optional on a Ship** (`?ShipName`): a freshly built ship may have no name yet. "Unnamed" is modelled as the *absence* of a `ShipName` (`null`), never as an empty `ShipName` — absence and empty value are different things. |
| **ShipClass** | Value Object (enum) | The ship's class, from smallest to largest: Corvette, Frigate, Destroyer, Cruiser, Battleship. Drives the hull `max` (and, later, the power budget and slot count). |
| **ShipHull** | Value Object | Structural points as `{ current, max }`, with the invariant `0 <= current <= max`. `max` derives from the `ShipClass`; `current == max` at build time. Minimal for now — no damage/repair behavior until a test requires it. |
| **BuildShip** | Command (use case) | Phase 1 use case: direct creation of a ship. The handler calls `Ship::build()`. |

### Layers — how a ship is created

Three distinct concerns, kept separate:

1. **Object construction (domain)** — *how* a `Ship` comes to exist. A single
   entry point, `Ship::build(...)`, guarantees the invariants.
2. **Use case (application)** — *why/in what context* one is created. A Command
   (`BuildShip`) that ultimately calls `Ship::build()`. Several commands may
   share the same constructor.
3. **Fixtures / tests** — instantiate the aggregate directly via `Ship::build()`,
   bypassing the bus and handlers. Seeding a test DB is not a business use case,
   so it has no Command.

## Phase 1bis — Fleets

| Term | Kind | Definition |
|---|---|---|
| **Fleet** | Aggregate Root | A named group of ships operating under one command. Has its own identity (`FleetId`) and its own lifecycle: a fleet is **formed**, ships are **assigned** and **detached**, and it is eventually **disbanded** — none of which creates or destroys a `Ship`. Holds its members **by identity** (`ShipId[]`), never as `Ship` objects. |
| **FleetId** | Value Object | The fleet's synthetic identity: a UUIDv7, generated in the domain. Same contract as `ShipId` — the second identifier, hence the one that justifies extracting a shared `Identifier` base. |
| **FleetName** | Value Object | The fleet's name. Non-empty, length-bounded, trimmed. **Required**, unlike `ShipName`: a fleet is formed *as* something, whereas a ship exists before being christened. |
| **Flagship** | Role (not an entity) | The ship from which the fleet is commanded. A **designation the fleet holds** (`Fleet.flagshipId`), not a kind of ship — the same vessel is an ordinary member in one fleet and the flagship in another. Invariant: **when a fleet has a flagship, it is one of that fleet's own ships**. A fleet may have none — see *Degraded fleets*. |
| **FormFleet** | Command (use case) | Bring a fleet into existence. Naval "form up", not a generic create. |
| **DisbandFleet** | Command (use case) | End a fleet's existence. Its ships survive and return to being unassigned — disbanding a fleet destroys no `Ship`. |
| **AssignShip** | Command (use case) | Place a ship under a fleet's command. Preferred over "add": it names the order given, not the mutation of a list. |
| **DetachShip** | Command (use case) | Release a ship from a fleet; the ship persists, unassigned. Deliberately **not** "remove", which would blur two different events — a ship leaving a fleet, and a ship being destroyed. The latter is a consequence, not a command on `Fleet`. Detaching the **flagship** is allowed: the fleet is left without one rather than promoting a successor behind the user's back. |
| **PromoteToFlagship** | Command (use case) | Designate one of the fleet's ships as its flagship. A ship is promoted *to* the role; the role itself is not promoted. If the fleet already has a flagship, the promotion **replaces** it and the former flagship stays an ordinary member — this is the real naval act of *shifting the flag*, one atomic order. Refusing it would force detaching an intact ship just to move the flag. |

### Degraded fleets

A fleet has states that are **legal but diminished**. They are surfaced to the
user, never prevented by the aggregate:

- **No flagship.** The flagship was detached (destroyed, reassigned) and no
  successor has been designated yet.
- **Fewer than two ships, down to none.** "At least two ships" is a rule of
  **formation**, not an invariant of life: a fleet is formed with a real group,
  but attrition may reduce it to a single ship, or to none at all. A fleet never
  disbands itself — `DisbandFleet` is a decision, not a consequence.
- **Wrecks still enlisted.** Destroying a ship does not detach it. `Fleet` holds
  `ShipId`, never `Ship`, so it cannot see a hull at zero — nothing about
  destruction happens inside the aggregate. A wreck leaves through `DetachShip`,
  by hand or by a scheduled sweep, never on its own.

Modelling these as invariants would mean a fleet vanishing or reshuffling its
command on its own, behind the user's back. The domain keeps the degraded state
and the UI names it ("fleet without a flagship", "fleet reduced to one ship",
"fleet down to wrecks").

The word for these states is **not "invalid"**: such a fleet is a perfectly valid
aggregate — consistent, persistable, reloadable. It is not **operational**.
Calling it invalid invites the next reader to "repair" the aggregate with a guard
in `detach()`. What the state gates is mobilisation, not existence.

Two levels answer that question, and they are not interchangeable:

- what `Fleet` can judge **alone** — how many ships are enlisted, whether a
  flagship is designated;
- what needs the ships themselves — "the flagship is a wreck", "over half the
  hulls are critical". The aggregate holds no `HullStatus`, so these belong to a
  **domain service** receiving the fleet *and* the ships concerned.

Consequence: since wrecks stay enlisted, a count of members is a count of
*enlisted* ships, never of *usable* ones. No aggregate-level check can mean more
than that.

### Two ship lists, two questions

`ListShips` cannot serve both the hangar and a fleet's contents — they are not two
filters over one list, but two screens answering different questions:

- **`ListAvailableShips`** — "which ship should I deploy?" Unassigned ships only.
- **`GetFleet`** — "what is this fleet made of?" The fleet *and* its ships in one
  read model, since a detail screen needs both at once.

The write side references across aggregates by identity; the read side is free to
join. That asymmetry is deliberate, not an inconsistency.

## Phase 2 — Components & designs

Inspired by the ship designer of 4X games (Stellaris): ships are built from
**designs**, designs are composed of **components** picked from a catalog.

### Three levels, three copies

The same reactor means three different things depending on where it is read:

| Level | What a reactor is there | Changes when |
|---|---|---|
| **Catalog** | A `ComponentModel` on offer: "A Mk1", its maker, its power output, whether it is still sold | The catalog edits or discontinues it |
| **Shipyard** | A `Component` fitted on a `ShipDesign` | The design is revised |
| **Ship** | A `ShipComponent` in the ship's `Loadout` (later) | The ship is upgraded or retrofitted |

The words follow the levels: the catalog sells **models**; designs and ships carry
**components**. "The A Mk1 model is no longer sold" / "my corvette has two
components".

Each level **copies** the characteristics it needs from the level above, at the
moment of use. It never reads them back. This is the classic order/address case of
DDD: an order keeps the shipping address and price *as they were*, whatever later
happens to the customer's profile or the product's price. Hence:

- discontinuing a component model — or even deleting it from the catalog — leaves every
  design and every ship intact;
- revising a design leaves every ship built from it intact, until it is upgraded;
- a ship can check its own rules without querying the catalog or the shipyard.

The copy freezes **everything**, including mistakes: fixing a typo in a
manufacturer's name in the catalog does not propagate. "As fitted" is the
intended meaning, not a side effect.

| Term | Kind | Definition |
|---|---|---|
| **ComponentModel** | Aggregate Root (Catalog) | A component **model** on offer, e.g. reactor "A Mk1". Has a `ComponentType`, a `ComponentSize` (weapons and defense only), a `Manufacturer`, a designation, and either a power output (reactors) or a power draw (everything else). Enters the catalog through `ComponentModel::release()`. **Interchangeable units**: two units of the same model cannot be told apart, so a design line has no identity of its own (see `ShipComponent` in reserved terms). |
| **ComponentModelId** | Value Object | The model's identity, an `Identifier` like `ShipId` and `FleetId`. |
| **ComponentType** | Value Object (enum) | What a component does, which decides where it goes on a hull: **reactor** and **FTL drive** (core), **weapon**, **armor** and **shield** (defense), **auxiliary** (everything else: jammer, cloaking device…). |
| **ComponentSize** | Value Object (enum) | **S**, **M** or **L**, for weapons and defense only. Each size costs capacity points: S = 1, M = 2, L = 4 (1 L = 2 M = 4 S). Core and auxiliary components have no size. |
| **Component** | Value Object | What a design or a ship carries: the characteristics of a `ComponentModel` **copied at the moment of use** (designation, manufacturer, type, size, power). Also keeps the source `ComponentModelId`, for **traceability only**. Nothing reads the catalog through it. Belongs to the `ShipDesign` aggregate (`Domain/ShipDesign`), not to the catalog: it is the design's copy. **No identity**: two components copied from the same model are equal. |
| **Discontinue** | Command (use case) | Stop the production and sale of a component model. It disappears from what can be fitted in a **new** design or revision. Existing designs and ships are untouched. A state change, **not a deletion**: the catalog keeps its history. **Idempotent**: discontinuing an already discontinued model is a no-op, like detaching a ship that is not enlisted. |
| **HullLayout** | Value Object | What a hull offers, given by its `ShipClass` for now: the **core slots** (one reactor, mandatory; one FTL drive, optional), a **weapon capacity** and a **defense capacity** (in S points), and a number of **auxiliary slots**. When sections arrive, the layout will come from the chosen sections instead: the rest of the model only reads a `HullLayout`. |
| **Slot** | Concept | A place for exactly **one** component, regardless of size: the core slots and the auxiliary slots. Weapons and defense have no slots, only a capacity. |
| **Capacity** | Concept | A budget of S points for weapons, and another for defense. Components are mixed freely as long as their sizes add up to no more than the capacity: a capacity of 3 takes S+S+S or M+S, never L. Unused capacity is allowed. |
| **ShipDesign** | Aggregate Root (Shipyard) | A named plan for one `ShipClass`: the `Component`s fitted on its `HullLayout`. **Always valid**: it has a reactor, at most one component per slot, weapons and defense within their capacities, every component in a place made for its type, and a `PowerBalance` that is never negative. An invalid combination is a draft in the UI, never a `ShipDesign`. Replaces the reserved term *ShipBlueprint*. |
| **ShipDesignId** | Value Object | The design's identity. |
| **Revision** | Concept | Editing a design produces a new **revision** of it. Ships keep the revision they were built or last upgraded with. |
| **PowerBalance** | Value Object | Power supplied by the reactor minus power drawn by the other components. An invariant of `ShipDesign`: it is never negative. Replaces the reserved term *PowerBudget*. |
| **Obsolete** | State of a `ShipDesign` | The design fits at least one discontinued component. Still a valid design, and its ships are still valid ships. Like *degraded* fleets, it is **surfaced**, not prevented. Open: can new ships still be built from it? (Probably not: a discontinued component can no longer be bought, and there is no stock yet.) |
| **Loadout** | Value Object | The components fitted on a ship, copied from a design revision. A ship changes its loadout only through a design, never component by component. |
| **BuildShip** | Command (use case) | *Evolves*: builds a ship **from a `ShipDesign`**: class and loadout come from its latest revision. Replaces the reserved term *BuildFromBlueprint*. |
| **UpgradeShip** | Command (use case) | Bring a ship to the **latest revision of its own design**. |
| **RetrofitShip** | Command (use case) | Switch a ship to **another design** of the same `ShipClass`. |
| **DesignShip** | Command (use case) | Bring a new `ShipDesign` into existence. Also the end of the **custom** path: a ship composed component by component in the UI is a draft, which becomes a design on validation, before `BuildShip` uses it. |

### Hull layouts

Weapon and defense capacities are in S points. Every hull has one reactor slot
(mandatory) and one FTL drive slot (optional).

| ShipClass | Weapon capacity | Defense capacity | Auxiliary slots |
|---|---|---|---|
| Corvette | 3 | 3 | 1 |
| Frigate | 5 | 5 | 2 |
| Destroyer | 5 | 6 | 2 |
| Cruiser | 12 | 16 | 3 |
| Battleship | 24 | 24 | 4 |

Because a ship's loadout only ever comes from a valid design, the ship needs **no
power rule of its own**: the invariant lives in one place, `ShipDesign`.

**Always a design, even for one-off ships.** Two paths in the UI, one in the domain:
- *quick build*: pick an existing design, `BuildShip`;
- *custom build*: compose component by component (a UI draft), validate, `DesignShip`,
  then `BuildShip` from it.
  The design **name is asked at validation**. The UI may pre-fill a suggestion, but
  the user confirms it: the domain only ever receives a chosen name, never a
  generated one.

Changing a single component on an existing ship follows the same rule: derive a new
design, then `RetrofitShip`. There is no command that edits a ship's loadout directly.

## Reserved terms (Phase 2+)

Named now to keep the language consistent; not modelled yet.

| Term | Meaning |
|---|---|
| **CommissionShip** | Bringing an already-built ship into active service — a lifecycle step, distinct from construction. Candidate home for the **eligibility** rules (named, fully built, required components fitted): `Ship::commission()` enforces them once, and only a commissioned ship can be assigned to a fleet. Eligibility is a property of the ship; **availability** (not serving in a fleet) is a relation between aggregates — two different rules. |
| **Section** | A part of a hull (bow, core, stern) with alternative variants to choose from. The `HullLayout` then comes from the chosen sections, no longer from the `ShipClass` directly. Capacities already let a design mix sizes freely, which covers part of what sections bring. Trigger: a hull whose variants differ by more than their weapon/defense mix (e.g. auxiliary slots, core). |
| **ShipComponent** | A physical, **identified** piece fitted on a ship (`ComponentId`), with its own lifecycle: wear, removal from one ship and refitting on another. An Entity, created by `BuildShip` from a design's `Component`, which stays a value: a design is a plan, not a set of physical pieces. Replaces the reserved term *ComponentUnit*. Trigger: the first rule that tells two pieces of the same model apart. |
| **Stock** | How many units of a component are on hand. A third concept, distinct from the catalog (what exists) and the loadout (what is fitted). Would decide whether an obsolete design can still be built. |
| **Mark** | The generation of a component within a line (Mk1, Mk2), with a *supersedes* relation between them. Not needed while upgrades go through designs. |
| **Admiral** | The officer commanding a fleet. Will be an **Aggregate Root** (name, service number, specialty), not a value object — hence deliberately left out of Phase 1bis rather than modelled as a string and migrated later. A `Fleet` will reference one by identity. Trigger: when an admiral gains *behaviour* (a specialty that alters the fleet, a unique service number), not while it is only a name. |
| **ShipPower** | A ship's combat strength, derived from its class and its `Loadout` (armour, weapons, defences, special abilities). Always computed, never entered. Not to be confused with `PowerBalance` (energy). Trigger: `Loadout`. |
| **FleetPower** | A fleet's **estimated** combat strength, aggregated from its members' `ShipPower`. Never stored on `Fleet`: the aggregate holds `ShipId`, not `Ship`, so it is computed on the read side (or by a domain service). Open: whether wrecks and damaged hulls count — wrecks stay enlisted (see *Degraded fleets*). |
| **FleetActivity** | What the fleet is doing right now: in transit, in orbit around a planet, docked at a shipyard, engaged in combat. Deliberately **not** "Status", which would collide with `HullStatus` and with a fleet's degraded state. Open: some activities imply a `Location` (docked ⇒ a shipyard, FTL transit ⇒ no planet) — the two may become a single value object so that incoherent pairs cannot be represented. |
| **Readiness** | Whether a fleet is **operational** or in a degraded state (no flagship, fewer than two ships, none at all — see *Degraded fleets*). Distinct from `FleetActivity` (what the fleet is doing) and from `HullStatus` (the state of one ship). The part `Fleet` can judge alone (ship count, flagship designated) is derivable today; the part needing the ships themselves (wrecked flagship, critical hulls) belongs to a domain service. |
| **Location** | Where a fleet is: a star system, a planet within one, or in FTL transit between two systems. To be detailed. Open: whether unassigned ships have a location too (hangar, shipyard). |
| **Manufacturer** | The maker of a ship or of a component. For components, it is copied into every `ComponentModel` (Phase 2). For ships, it may influence stats (e.g. base hull) and namespaces serial numbers. |
| **SerialNumber** | A ship's business identifier assigned by its `Manufacturer`. **Not** the ship's identity: not globally unique (two manufacturers may reuse the same value) — business uniqueness is the composite `(Manufacturer, SerialNumber)`. A `Ship` attribute, distinct from its `ShipId`. |
