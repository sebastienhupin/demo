
export default class Rule {
    constructor(operator, left, right, readOnly) {
        this.readOnly = readOnly || false;
        this.operator = operator;
        this.identifier = left;
        this.value = right;   
        this.parent = null;
        
        this.prepare();

    }
 
    hasParent () {
        return null === this.parent ? false : true;
    }    
        
    identifierIsReadOnly() {
        let identifier = this.identifier;
        if (identifier && 'undefined' !== typeof this.identifierSelected.readOnly && this.identifierSelected.readOnly) {
            return true;
        }
        return false;
    }    
    
    watch() {
        return {
            "identifier": this.identifier,
            "operator": this.operator,
            "value": this.value
        }
    }
    
    toString() {

        if (!this.identifier || !this.operator || null === this.value || '' === this.value) {
            return null;
        } 

        let value = this.value;

        if (
            '>=<' === this.operator
            || 'date' === this.identifierSelected.type
            || 'tag' === this.identifierSelected.type
        ) {
            value = `'${value}'`;
        }

        if (
            '>=<' === this.operator
            || 'string' === this.identifierSelected.type
            || 'enum' === this.identifierSelected.type
        ) {
            value = `${value}`;
        }
        
        if ('undefined' !== typeof this.identifierSelected['rule']) {
            let args = this.value.split(',');
            args.unshift(this.operator);
            return this.identifierSelected['rule'].apply(this, args);
        }

        return this.identifier + ' ' + this.operator + ' ' + value;
    }    
    
    operators() {
        if (null === this.identifier || 'undefined' === typeof this.identifier) return [];

        this.identifierSelected = _.findWhere(this.allowedIdentifiers, {id: this.identifier});
        let typeSelected = this.identifierSelected.type;

        return this.operatorType[typeSelected];
    }
    
    identifiers() {
        return this.allowedIdentifiers;
    }
    
    prepare() {
        this.allowedIdentifiers = [
            {
                'id': 'person.gender',
                'label': 'person.gender',
                'type': 'enum',
                'service': 'person_gender'
            },
            {
                'id': 'age',
                'label': 'age',
                'type': 'number'
            },

            {
                'id': 'person.typeMoral',
                'label': 'person.typeMoral',
                'type': 'enum',
                'service': 'person_moral_type'
            },
            {
                'id': 'person.isPhysical',
                'label': 'person.isPhysical',
                'type': 'boolean'
            },
            {
                'id': 'person.name',
                'label': 'person.name',
                'type': 'string'
            },
            {
                'id': 'person.givenName',
                'label': 'person.givenName',
                'type': 'string'
            },
            {
                'id': 'person.birthDate',
                'label': 'person.birthDate',
                'type': 'date'
            },
            {
                'id': 'function.functionType.functionType',
                'label': 'function.functionType.functionType',
                'type': 'enum',
                'service': 'function_type'
            },
            {
                'id': 'function.functionType.mission',
                'label': 'function.functionType.mission',
                'type': 'enum',
                'service': 'mission'
            },
            {
                'id': 'function.endDate',
                'label': 'function.endDate',
                'type': 'date'
            },
            {
                'id': 'function.activity.name',
                'label': 'function.activity.name',
                'type': 'string'
            },
            {
                'id': 'education.educationCurriculum.education.educationComplement.name',
                'label': 'education.educationCurriculum.education.educationComplement.name',
                'type': 'enum',
                'service': 'equipment_group'
            },
            {
                'id': 'education.educationCurriculum.year',
                'label': 'education.educationCurriculum.year',
                'type': 'date'
            },
            {
                'id': 'education.educationCurriculum.level',
                'label': 'education.educationCurriculum.level',
                'type': 'string'
            },
            {
                'id': 'education.educationCurriculum.endDate',
                'label': 'education.educationCurriculum.endDate',
                'type': 'date'
            },
            {
                'id': 'education.educationCurriculum.education.educationCategory.label',
                'label': 'education.educationCurriculum.education.educationCategory.label',
                'type': 'string'
            },
            {
                'id': 'education.educationCurriculum.education.educationCategory.educationTypeEnum',
                'label': 'education.educationCurriculum.education.educationCategory.educationTypeEnum',
                'type': 'enum',
                'service': 'education'
            },
            {
                'id': 'education.teacher.name',
                'label': 'education.teacher.name',
                'type': 'string'
            },
            {
                'id': 'education.schoolYearBeginDate',
                'label': 'education.schoolYearBeginDate',
                'type': 'date'
            },
            {
                'id': 'education.acquired',
                'label': 'education.acquired',
                'type': 'boolean'
            },
            {
                'id': 'tags',
                'label': 'tag',
                'type': 'tag'
            }
        ];

        this.operatorType = {
            'identifier':[
                {
                    'value': '=',
                    'label': 'equal_to'
                }
            ],    
            'number':[
                {
                    'value': '=',
                    'label': 'equal_to'
                },
                {
                    'value': '!=',
                    'label': 'not_equal_to'
                },
                {
                    'value': '>',
                    'label': 'greather_than'
                },
                {
                    'value': '>=',
                    'label': 'greater_than_or_equal'
                },
                {
                    'value': '<',
                    'label': 'less_than'
                },
                {
                    'value': '<=',
                    'label': 'less_than_or_equal'
                },
                {
                    'value': '>=<',
                    'label': 'between'
                }                
            ],                
            'string': [
                {
                    'value': 'like',
                    'label': 'contains'
                }
                //,
                //{
                //    'value': 'not',
                //    'label': 'does_not_contain'
                //}
                
            ],
            'boolean': [
                {
                    'value': '=',
                    'label': 'is'
                },
            ],
            'choice': [
                {
                    'value': '=',
                    'label': 'is'
                },
            ],
            'choices': [
                {
                    'value': 'in',
                    'label': 'are'
                },
            ],            
            'enum': [
                {
                    'value': 'like',
                    'label': 'is'
                }
                //,
                //{
                //    'value': 'not',
                //    'label': 'not_is'
                //}
                
            ],            
            'date': [
                {
                    'value': '=',
                    'label': 'equal_to'
                },
                {
                    'value': '!=',
                    'label': 'not_equal_to'
                },
                {
                    'value': '>',
                    'label': 'greather_than'
                },
                {
                    'value': '>=',
                    'label': 'greater_than_or_equal'
                },
                {
                    'value': '<',
                    'label': 'less_than'
                },
                {
                    'value': '<=',
                    'label': 'less_than_or_equal'
                }                
            ],
            'tag': [
                {
                    'value': '=',
                    'label': 'is'
                }
                
            ]            
        };
    }
    getClass() {
        return this.className();
    }
    className(){
        return 'Rule';
    }
}
