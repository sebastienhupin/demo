import Rule from './rule.class.js';
import OrganizationRule from './organizationRule.class.js';
import PeopleRule from './peopleRule.class.js';

export default class OrganizationCurrentRule extends OrganizationRule {
    constructor() {
        super('current');
    }

    className(){
        return 'OrganizationCurrentRule';
    }
}
