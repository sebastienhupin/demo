import OrganizationRule from './organizationRule.class.js';

export default class PeopleExceptionRule extends OrganizationRule {
    constructor() {
        super('and', null, null, false);
        this.identifier = `notinpeople`;
        this.operator = 'and';
    }

    toString() {
        return this.value ? `(${this.identifier}('${this.value}'))` : null;
    }

    className(){
        return 'PeopleExceptionRule';
    }
}
