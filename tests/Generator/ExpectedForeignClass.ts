import { type MyClass } from 'model/Dayploy/JsDtoBundle/Tests/src/Entity/MyClass'

export interface ForeignClass {
  id: string
  myClass: MyClass | null
}
