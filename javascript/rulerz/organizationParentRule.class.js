import Rule from './rule.class.js';
import OrganizationRule from './organizationRule.class.js';
import PeopleRule from './peopleRule.class.js';

export default class OrganizationParentRule extends OrganizationRule {
    constructor() {
        super('parent');
    }

    className(){
        return 'OrganizationParentRule';
    }
}
