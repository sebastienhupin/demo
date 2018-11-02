import LogicalRule from './logicalRule.class.js';

export default class MasterRule extends LogicalRule {
    constructor(operator = 'or') {
        super(operator, null, null, true);
        this.isGrouping = true;
    }

    className(){
        return 'MasterRule';
    }
}
