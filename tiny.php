<?php

interface Cores
{
	static function lexical($source);
	static function syntax($source);
}

class AnalyserEnum
{
	const LEXICAL = "lexical";
	const SYNTAX = "syntax";
}

class LexicalAnalyser
{
	static $symbol_table = [];
	
	static function analyse($source)
	{
		$tokens = self::clear_source($source);

		self::token_recognizer($tokens);
	}
	
	private function clear_source($source)
	{
		return str_replace("\s","",$source);
	}

	private function token_recognizer($tokens)
	{
		$expression = self::regular_expression();
		
		for($i=0;$i<sizeof($expression);$i++)
		{
			if(preg_match("/${expression[$i]}/",$tokens,$token))
			{
				for($j=0;$j<sizeof($token);$j++)
				{
					self::$symbol_table[] = $token[$j];
				}
			}
		}
		
		print_r(self::$symbol_table);
	}
	
	private function regular_expression()
	{
		return [
			"^function",
			"[^(?:\bfunction\b)]\w+",
			"\(",
			"\)",
			"\{",
			"\}",
		];
	}		
}

class SyntaxAnalyser
{
	static function analyse($source)
	{
		
	}
}

abstract class Analyser implements Cores
{
	static function lexical($source)
	{
		LexicalAnalyser::analyse($source);
	}
	
	static function syntax($source)
	{

	}
	
	abstract function with($analyser);
}

class Compiler extends Analyser
{
	private static $source;

	function init($source):Compiler
	{
		if(file_exists($source))
		{
			$resource = fopen($source,'r');
			self::$source = fread($resource,strlen($source));
		}

		return new self();
	}
	
	function with($analyser):Compiler
	{
		switch($analyser)
		{
			case AnalyserEnum::LEXICAL:
				parent::lexical(self::$source);
			break;
			case AnalyserEnum::SYNTAX:
				parent::syntax(self::$source);
			break;
		}

		return new self();
	}
}

class TinyCompiler extends Compiler
{
	private static $path = __DIR__.DIRECTORY_SEPARATOR;

	static function run($argv)
	{
		self::$path = self::$path.$argv[1];
		Compiler::init(self::$path)->with(AnalyserEnum::LEXICAL);
	}
}

TinyCompiler::run($argv);
