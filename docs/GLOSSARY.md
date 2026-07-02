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
| **Manufacturer** | The maker of a ship. May influence stats (e.g. base hull) and namespaces serial numbers. |
| **SerialNumber** | A ship's business identifier assigned by its `Manufacturer`. **Not** the ship's identity: not globally unique (two manufacturers may reuse the same value) — business uniqueness is the composite `(Manufacturer, SerialNumber)`. A `Ship` attribute, distinct from its `ShipId`. |