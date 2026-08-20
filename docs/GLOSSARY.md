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
| **ShipClass** | Value Object (enum) | The ship's class (e.g. Corvette, Destroyer, Cruiser, Battleship). Drives the hull `max` (and, later, the power budget and slot count). |
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
| **Flagship** | Role (not an entity) | The ship from which the fleet is commanded. A **designation the fleet holds** (`Fleet.flagshipId`), not a kind of ship — the same vessel is an ordinary member in one fleet and the flagship in another. Invariant: the flagship is always one of the fleet's own ships. |
| **FormFleet** | Command (use case) | Bring a fleet into existence. Naval "form up", not a generic create. |
| **DisbandFleet** | Command (use case) | End a fleet's existence. Its ships survive and return to being unassigned — disbanding a fleet destroys no `Ship`. |
| **AssignShip** | Command (use case) | Place a ship under a fleet's command. Preferred over "add": it names the order given, not the mutation of a list. |
| **DetachShip** | Command (use case) | Release a ship from a fleet; the ship persists, unassigned. Deliberately **not** "remove", which would blur two different events — a ship leaving a fleet, and a ship being destroyed. The latter is a consequence, not a command on `Fleet`. |
| **PromoteToFlagship** | Command (use case) | Designate one of the fleet's ships as its flagship. A ship is promoted *to* the role; the role itself is not promoted. |

### Two ship lists, two questions

`ListShips` cannot serve both the hangar and a fleet's contents — they are not two
filters over one list, but two screens answering different questions:

- **`ListAvailableShips`** — "which ship should I deploy?" Unassigned ships only.
- **`GetFleet`** — "what is this fleet made of?" The fleet *and* its ships in one
  read model, since a detail screen needs both at once.

The write side references across aggregates by identity; the read side is free to
join. That asymmetry is deliberate, not an inconsistency.

## Reserved terms (Phase 2+)

Named now to keep the language consistent; not modelled yet.

| Term | Meaning |
|---|---|
| **ShipBlueprint** | The *conceptual* ship: a reusable plan. Realized into a physical `Ship` via the `BuildFromBlueprint` command. |
| **BuildFromBlueprint** | Command (use case): build a physical `Ship` from a `ShipBlueprint`. |
| **CommissionShip** | Bringing an already-built ship into active service — a lifecycle step, distinct from construction. |
| **Module** | A piece of equipment installed on a ship. |
| **Slot** | A mounting point on a ship where a Module can be installed. |
| **PowerBudget** | Energy produced vs. consumed on a ship; an installation invariant. |
| **Admiral** | The officer commanding a fleet. Will be an **Aggregate Root** (name, service number, specialty), not a value object — hence deliberately left out of Phase 1bis rather than modelled as a string and migrated later. A `Fleet` will reference one by identity. Trigger: when an admiral gains *behaviour* (a specialty that alters the fleet, a unique service number), not while it is only a name. |
| **Manufacturer** | The maker of a ship. May influence stats (e.g. base hull) and namespaces serial numbers. |
| **SerialNumber** | A ship's business identifier assigned by its `Manufacturer`. **Not** the ship's identity: not globally unique (two manufacturers may reuse the same value) — business uniqueness is the composite `(Manufacturer, SerialNumber)`. A `Ship` attribute, distinct from its `ShipId`. |