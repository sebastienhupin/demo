import Rule from './rule.class.js';

export default class LogicalRule extends Rule {
    constructor(operator, left, right, readOnly) {
        super(operator, null, null, true);
        this.isGrouping = false;
        this.children = [];
        this.parent = null;        
        
        this.operatorType = {
            'logical': [
                {
                        'value': 'or',
                        'label': 'any'
                },
                {
                        'value': 'and',
                        'label': 'all'
                }
            ]    
        };

        this.init(operator, left, right, readOnly);
    }

    className(){
        return 'LogicalRule';
    }
    
    init(operator, left, right, readOnly) {
        if (null !== left) {            
            if (left instanceof LogicalRule && !left.isGrouping && left.children.length > 0) {
                var childs = left.children;
                for (var i= 0; i<childs.length;i++) {
                    var c = childs[i];
                    c.parent = this;
                    this.add(c);
                }
                left.children = [];
            }
            else {
                this.add(left);
            }
        }
        if (null !== right) {
            this.add(right);
        }      
    }
    
    add(rule, afterRule) {
        if ('undefined' === typeof afterRule) {
            this.children.push(rule);
        }
        else {
            let index = this.children.indexOf(afterRule);
            this.children.splice(index+1, 0, rule);            
        }

        if(_.isString(rule))
            return

        rule.parent = this;          
    }
    
    hasChildren () {
        return this.children.length === 0 ? false : true;
    }    
    
    remove(rule) {
        let index = this.children.indexOf(rule);
        if (-1 !== index) {
            this.children.splice(index, 1);
        }
        return this;        
    }

    operators() {
        return this.operatorType['logical'];        
    }

    toString() {
        if (null === this.operator) return null;
        
        if (this.hasChildren()) {
            let rules = [];
            let child = this.children;
            for (var i = 0; i < child.length; i++) {
                if(child[i].value === "none")
                    return null;
                let rule = child[i].toString();
                if (null !== rule) {
                    rules.push(rule);
                }                
            }
            if (rules.length > 0) {
                return '(' + rules.join(' ' + this.operator + ' ') + ')';
            }
            return null;
        }

        return null;
    }
}
