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

	public $vars = [];

    public function __construct(EntityFunction $function) {
        $this->function = $function;
	    foreach($function->arguments as $arg) {

	    }
    }

    public function convert() {
        if(!$this->function->tokens) { // empty body
            return "";
        }
        $tokens = $this->function->tokens;
        while($tokens->valid()) {
            if($tokens->is(Tokenizer::MACRO_STRING)) {
                $stmt = $tokens->getStmtName();
                $this->{"parse$stmt"}($tokens);
            } else {
                $this->parseExpression($tokens);
            }
        }
        return "";
//        return $this->statments($this->function->stmts);
    }

	/**
	 * @param array $stmts
	 * @return string
	 */
	public function statements(array $stmts) {
		$lines = [];
		foreach($stmts as $stmt) {
			/* @var \PhpParser\Node $stmt */
			$method = str_replace('_', '', $stmt->getType());
			$code = $this->$method($stmt);
			if(is_array($code)) {
				foreach($code as $item) {
					$lines[] = $item;
				}
			} else {
				$lines[] = $code;
			}
		}

		return  implode("\n    ", $lines);
	}

	/**
	 * @param mixed $expr
	 * @param string $name
	 * @throws \RuntimeException
	 * @return \Koda\Compiler\ZendEngine\Zval
	 */
	public function zval($expr, $name = null) {
		$var = new Zval($this);
		if($name) {
			$this->vars[$name] = $var;
		}
		if($expr instanceof Scalar) {
			$var->setScalar($expr->value);
		} elseif($expr instanceof Expr) {
//			$code = $this->expr();
//		} else {
			drop($expr);
			throw new \RuntimeException("unsupported expr: ".$expr->getType());
		}

		return $var;
	}


	public function exprUnaryMinus(Expr $expr) {
		if($expr->expr instanceof Scalar) {
			return $this->zval($expr->expr);
		}
	}

    public function stmtReturn($value) {
	    $return = $this->zval($value->expr, 'result');
	    /** @var Zval $return */
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
            }
        } else {
            return "RETURN_ZVAL($value, 1);";
        }
    }


}