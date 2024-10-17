import { type ForeignClass } from "@model/Dayploy/JsDtoBundle/Tests/src/Entity/ForeignClass"

export interface MyClass {
  id: string
  number: number
  name: string
  foreignClasses: ForeignClass[]
  references: number[]
}
