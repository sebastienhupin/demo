import Rule from './rule.class.js';

export default class PeopleRule extends Rule {
    constructor(values) {
        super(null, 'people', values, true);
    }
        
    toString() {
        return this.value ? `(${this.identifier}('${this.value}'))` : null;
        //return this.value ? `((organization(parent) and (${this.identifier}('${this.value}'))))` : null;
        //return this.value ? `((organization(current) or organization(children)) and (${this.identifier}('${this.value}')))` : null;
    }

    className(){
        return 'PeopleRule';
    }
}