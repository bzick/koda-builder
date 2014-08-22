<?php

namespace Koda\Compiler\ZendEngine;

use Koda\Entity\EntityFunction;
use Koda\Entity\Types;
use Koda\Tokenizer;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Return_;

class Scope {
    /**
     * @var \Koda\Entity\EntityFunction
     */
    public $function;

    /**
     * @var Zval[]
     */
    public $vars = [];

    public $reads = 0;
    public $writes = 0;

    public function __construct(EntityFunction $function) {
        $this->function = $function;
        foreach($this->function->arguments as $argument) {
            $this->vars[$argument->name] = (new Zval($this, $argument->name))->setArgument($argument);
        }
    }

    public function convert() {
        if(!$this->function->tokens) { // empty body
            return "";
        }

        $code = [];
        $tokens = $this->function->tokens;
        while($tokens->valid()) {
            if($tokens->is(Tokenizer::MACRO_STRING)) {
                $stmt = $tokens->getStmtName();
                $code[] = $this->{"parse$stmt"}($tokens);
            } else {
                $code[] = $this->parseExpression($tokens);
            }
        }
        return implode("\n\t", $code);
//        return $this->statments($this->function->stmts);
    }

    public function parseReturn(Tokenizer $tokens) {
        if($tokens->valid()) {
            $return = $this->parseExpression($tokens->next());
            dump("$return");
            if($return->isScalar()) {
                switch($return->type) {
                    case Types::BOOLEAN:
                        return $return->value ? "RETURN_TRUE;" : "RETURN_FALSE;";
                    case Types::DOUBLE:
                        return "RETURN_DOUBLE({$return->value});";
                    case Types::INT:
                        return "RETURN_LONG({$return->value});";
                    case Types::NIL:
                        return "RETURN_NULL();";
                    case Types::STRING:
                        return "RETURN_STRINGL(\"".addslashes($return->value)."\", ".strlen($return->value).");";
                    default:
                        drop("unknown type ".Types::getTypeCode($return->type));
                }
            } else {
                return "RETURN_ZVAL($return, 1);";
            }
        } else {
            return "return;";
        }
    }

    public function parseExpression(Tokenizer $tokens) {
        $op   = false; // last exp was operator
        $cond = false; // was comparison operator
        $return = $this->parseTerm($tokens);
        while($tokens->valid()) {
            if($tokens->is(';')) {
                $tokens->next();
                return $return;
            } elseif ($tokens->is(Tokenizer::MACRO_BINARY)) { // binary operator: $a + $b, $a <= $b, ...
                if ($tokens->is(Tokenizer::MACRO_COND)) { // comparison operator
                    if ($cond) {
                        break;
                    }
                    $cond = true;
                } elseif ($tokens->is(Tokenizer::MACRO_BOOLEAN)) {
                    $cond = false;
                }
                $op = $tokens->getAndNext();
            }
            if($op) {
                $zval = $this->parseTerm($tokens);
                $return->op($op, $zval);
            }
        }


        return $return;
    }

    public function parseTerm(Tokenizer $tokens) {
        $zval = new Zval($this);
        if ($tokens->is(Tokenizer::MACRO_UNARY)) {
            $unary = $tokens->getAndNext();
        } else {
            $unary = "";
        }
        if ($tokens->is(T_LNUMBER, T_DNUMBER)) {
            return $zval->setScalar($tokens->getAndNext() * intval($unary."1"));
        } elseif($tokens->is(T_CONSTANT_ENCAPSED_STRING)) {
            $string = $tokens->getAndNext();
            if($string{0} == '"') {
                $string = substr($string, 1, -1);
            } else {
                $string = addslashes(substr($string, 1, -1));
            }
            return $zval->setScalar($string);
        } elseif($tokens->is(T_STRING)) {
            if($tokens->isSpecialVal()) {
                return $zval->setScalar(json_decode(strtolower($tokens->getAndNext())));
            } else {
                throw new \RuntimeException("callback or constant: ".$tokens->current());
            }
        } elseif($tokens->is(T_VARIABLE)) {
            return $this->parseVariable($tokens);
        } else {
            throw new \RuntimeException("Another tokens not ready yet: ".$tokens->current());
        }
    }

    public function parseVariable(Tokenizer $tokens) {
        $name = substr($tokens->getAndNext(), 1);
        if($tokens->is(T_OBJECT_OPERATOR, T_DOUBLE_COLON)) {
            drop("parse object");
        } elseif($tokens->is('[')) {
            drop("parse array");
        } elseif($tokens->is('(')) {
            drop("parse call");
        } else {
            return $this->variable($name);
        }
    }

    public function variable($name) {
        if(isset($this->vars[$name])) {
            $this->reads++;
            return $this->vars[$name];
        } else {
            $zval = new Zval($this);
            $this->vars[$name] = $zval;
            return $zval;
        }
    }

}