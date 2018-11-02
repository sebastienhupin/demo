import LogicalRule from './logicalRule.class.js';

export default class NetworkRule extends LogicalRule {
    constructor(type) {
        super('and', null, null, false);
        this.type = type || 'current';
        this.identifier = `network(${this.type})`;
        this.operator = 'and';
    }
    
    toString() {
        let rule = super.toString();

        if (null === rule) {
            return null;
        }
        this.identifier = `network(${this.type})`;
        return `${this.identifier} ${this.operator} ${rule}`;
    }

    className(){
        return 'NetworkRule';
    }
}
