import { type ForeignClass } from 'model/Dayploy/JsDtoBundle/Tests/src/Entity/ForeignClass'
import { type IntValuesEnum } from 'model/Dayploy/JsDtoBundle/Tests/src/Entity/IntValuesEnum'
import { type StringValuesEnum } from 'model/Dayploy/JsDtoBundle/Tests/src/Entity/StringValuesEnum'

export interface MyClass {
  id: string
  number: number
  name: string
  foreignClasses: ForeignClass[]
  references: number[]
  intEnum: IntValuesEnum
  stringEnum: StringValuesEnum
}
