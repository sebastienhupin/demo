import Rule from './rule.class.js';

export default class PredefinedRule extends Rule {
    constructor(value) {
        super(null, 'rule', value, false);
    }

    toString() {
        return (null === this.value) ? null : `${this.identifier}('${this.value}')`;
    }

    className(){
        return 'PredefinedRule';
    }
}
