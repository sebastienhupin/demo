/*!
 * Opentalent
 * http://opentalent.fr
 *
 * Copyright 2016 2iosopenservice
 * Author Sébastien Hupin
 */

/* References:

-   Udacity course on Programming Languages
-   Stanford course on Compilers
-   https://zaa.ch/jison/
-   https://github.com/stanistan/aql-parser-js/blob/master/src/lang/grammar.jison
-   https://github.com/stanistan/aql-parser-js/blob/master/src/lang/lexer.jisonlex
-   http://stackoverflow.com/questions/12566964/expression-ast-parser-from-expression-interpreter-demo/12568601#12568601
*/

/*
    This file must be compiled with jison
    > jison grammar.jison

    A expression is composed with some words
    ex :
    func(arg1,arg2) sera traduit par ce qui suit
    litteral LPAREN arg1 COMMA arg2 RPAREN
    {console.log($1,$2,$3,$4,$5,$6);$$ = $1($3,$5);}

    $$ correspond a la valuer retournée et enregistrée, pour une utilisation future
    $1 => litteral
    $2 => LPAREN
    $3 => arg1
    $4 => COMMA
    $5 => arg2
    $6 => RPAREN
*/

%{

%}


/* lexical grammar */
%lex
%options flex case-insensitive
%%

\s*\n\s*                  /* ignore */

[\w]?\"(\\.|[^\\"])*\"    return 'STRING_LITERAL_D';
[\w]?\'(\\.|[^\\'])*\'    return 'STRING_LITERAL_S';
[\w]?\,(\\.|[^\\'])*\'    return 'STRING_LITERAL_COMMA';

","                       return 'COMMA';

"("                       return 'LPAREN';
")"                       return 'RPAREN';

"not"                     return 'NOT';
"!"                       return 'NOT';

"in"                      return 'IN';
">="                      return 'GTE';
"<="                      return 'LTE';
">"                       return 'GT';
"<"                       return 'LT';
"=="                      return 'EQ';
"="                       return 'EQ';
"!="                      return 'NEQ';
">=<"                     return 'BETWEEN';

"like"                    return 'LIKE';
"~"                       return 'LIKE';
"!~"                      return 'NLIKE';
"ilike"                   return 'ILIKE';
"~*"                      return 'ILIKE';
"!~*"                     return 'NILIKE';

"and"                     return 'AND';
"&&"                      return 'AND';
"or"                      return 'OR';
"||"                      return 'OR';

[A-Za-z0-9_\-\.:]+        return 'IDENT';

\s+                       /* */
.                         return 'INVALID';
<<EOF>>                   return 'EOF';

"organization(current)"   return 'ORGANIZATION_CURRENT';
"organization(parent)"    return 'ORGANIZATION_PARENT';
"organization(children)"  return 'ORGANIZATION_CHILDREN';
"organization(all)"       return 'ORGANIZATION_ALL';
"network(all)"            return 'NETWORK_ALL'

/lex

/* operator associations and precedence */
%left LPAREN RPAREN
%left OR 
%left AND 
%right NOT
%nonassoc EQ LIKE ILIKE IN GTE GT LTE LT

/* start symbol */
%start statement

%% /* language grammar */

statement
    :   expression EOF
            {  
               //typeof console !== 'undefined' ? console.log($1) : print($1);
               return parser.yy.root;
            }
    |   query EOF
            {
                return $1;
            }
    ;

expression
    :   expression OR expression
            {}
    |   organization AND query
            {
                $1.add($3);
                $$ = $1;
            }
    |   network AND query
            {
                $1.add($3);
                $$ = $1;
            }
    |   newtwork
            {$$ = new LogicalRule("AND", $1, null, true);}
    |   organization
            {$$ = new LogicalRule("AND", $1, null, true);}
    |   LPAREN expression RPAREN
            {
                if ('undefined' !== typeof $2) {
                    $$ = parser.yy.root.add($2);
                }
            }
    ;

query
    :   query AND query
            {$$ = new LogicalRule('and', $1, $3);}
    |   query OR query
            {$$ = new LogicalRule('or', $1, $3);}            
    |   NOT query
            {$$ = new LogicalRule('not', $2, null);}
    |   literal operator literal
            {$$ = new Rule($2, $1, $3);}
    |   literal
            {$$ = new Rule("like", $1);}
    |   function
            {$$ = $1;}
    |   LPAREN query RPAREN
            {
                let rule = null
                if ($2 instanceof PeopleRule) {
                    rule = $2;
                }
                else {
                    rule = new LogicalRule('or', $2, null);
                    rule.isGrouping = true;
                }

                $$ = rule;
            }
    ;

organization 
    : ORGANIZATION_CURRENT
        {
            var o = new OrganizationCurrentRule();
            $$ = o;
        }
    | ORGANIZATION_PARENT
        {   
            var o = new OrganizationRule("parent");
            $$ = o;
        }
    | ORGANIZATION_CHILDREN
        {            
            var o = new OrganizationRule("children");
            $$ = o;
        }
    ;

network
    : NETWORK_ALL
        {
            var o = new NetworkRule("all");
            $$ = o;
        }
    ;

literal
    :   STRING_LITERAL_S
            {$$ = $1.substring(1, $1.length - 1);}
    |   STRING_LITERAL_D
            {$$ = $1.substring(1, $1.length - 1);}
    |   STRING_LITERAL_COMMA
            {$$ = $1;}
    |   IDENT
            {$$ = $1;}
    ;

operator
    : EQ
        {$$ = "=";}
    | NEQ
        {$$ = "!=";}
    | LIKE
        {$$ = "like";}
    | ILIKE
        {$$ = "like";}
    | NLIKE
        {$$ = "like";}
    | NILIKE
        {$$ = "like";}
    | GT
        {$$ = ">";}
    | GTE
        {$$ = ">=";}
    | LT
        {$$ = "<";}
    | LTE
        {$$ = "<=";}
    | BETWEEN
        {$$ = ">=<";}
    ;

function
    : literal LPAREN literal RPAREN
        {
            if ('people' === $1) {
                $$ = new PeopleRule($3);
            }
            else if ('rule' === $1) {
                $$ = new PredefinedRule($3);
            }
            else {
                $$ = new Rule("EQ", $1, $3);
            }
        }
    | literal LPAREN operator COMMA literal RPAREN
        {$$ = new Rule($3, $1, $5);}
    | literal LPAREN operator COMMA literal COMMA literal RPAREN
        {$$ = new Rule($3, $1, [$5,$7]);}
    ;
%%

    var Rule = require('./rule.class.js').default;
    var MasterRule = require('./masterRule.class.js').default;
    var LogicalRule = require('./logicalRule.class.js').default;
    var OrganizationRule = require('./organizationRule.class.js').default;
    var OrganizationCurrentRule = require('./organizationCurrentRule.class.js').default;
    var PeopleRule = require('./peopleRule.class.js').default;
    var PredefinedRule = require('./predefinedRule.class.js').default;
    var NetworkRule = require('./networkRule.class.js').default;

    exports.reset = function() {
        parser.yy.root = new MasterRule();    
    }
