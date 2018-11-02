
var RulerzParser = require('./rulerz.js').parser;
import Rule from './rule.class';

RulerzParser.reset =  function () {
    this.yy.root = new Rule('OR',null,null);
    this.yy.root.isGrouping = true;
    this.yy.root.isRoot = true;       
}

export default RulerzParser;