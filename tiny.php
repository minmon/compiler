<?php
/*
 * Common interface for important functions
 * 
 * @name    : Cores
 * @func    : lexical,syntax
 * @param   : $source
 * 
 * @Warning : Don't remove this line
 */
  
interface Cores
{
	static function lexical($source);
	static function syntax($source);
}

/*
 * Error handling class for invalid tokens
 * 
 * @name    : InvalidTokenHandler
 * @extends : Exception
 * @func    : __construct
 * @param   : $error_message,$code,$previous
 * 
 * @appendix: $previous define in null value for 
 * 			  unnested exception 
 */
  
class InvalidTokenHandler extends Exception
{
	function __construct($error_message)
	{
		parent::__construct($error_message,$code=0,$previous=null);
	}
}

/*
 * Enumerated list to store the types of analyser
 * in constant value.
 * 
 * @name    : AnalyserEnum
 * @const   : LEXICAL,SYNTAX
 * @value   : lexical,syntax
 * 
 * @warning : Don't remove this line
 */ 

class AnalyserEnum
{
	const LEXICAL = "lexical";
	const SYNTAX = "syntax";
}

/*
 * Lexical analyser class for lexical analysing
 * 
 * @name : LexicalAnalyser
 * @var  : $tokens=[],$raw_token,$lexical_grammar=[] 
 * @funcs: 
 * 
 * @func : analyser function is a warapper function. 
 *		   That will serve the analysing functionalities
 * 		   instead of other methods.
 * 
 * @func : token_init function will remove the space char
 * 		   and make a string of symbols.
 * 
 * @func : tokenizer function splits the stream of symbols
 * 		   as individula characters and concatenate them.
 * 
 * @func : get_tokens is a function and that will examine
 * 		   the concatenated characters by using the regular
 * 		   expression.
 * 
 * @func : next_tokens is a function that will remove the
 *         previous matched tokens from stream and return
 *         the new stream without the previous matched tokens.
 * 
 * @func : token_recognizer function that will recognize the
 * 		   tokens which are imported from tokenizer function
 *         and will throw the relevant errors,if the imported
 * 		   token is an invalid token.
 * 
 * @func : KEYWORD,SCOPE,IDENTIFIER functions are mocks.
 * 
 * @func : isLetter function will examine the skipped tokens
 *         and will thorw the relevant errors if they were non-
 * 		   alpahbets or not nulls. 
 */ 

class LexicalAnalyser
{
	private static $tokens=[],$raw_token;
	private static $lexical_grammar = [
		"(int)(.*)(=)([0-9])(;)",
		"(function)(.*)(\()(\))(\{)(.*)(\})",
	];
	
	static function analyse($source)
	{
		$init = self::token_init($source);		
		$tokens = self::tokenizer($init);
		
		self::token_recognizer($tokens);
	}
	
	private function token_init($source):String
	{	
		$split = preg_split("/(\s++)/",$source);
		$split = implode("",$split);
		
		return $split;
	}	
	
	private function tokenizer($init)
	{
		$token = null;
		
		for($i=0;$i<strlen($init);$i++)
		{
			$token .= $init[$i];
			
			if(self::get_tokens($token))
			{
				$init = self::next_tokens($init,$token);
				
				return self::tokenizer($init);
			}
		}
		
		return self::$tokens;
	}
	
	private function get_tokens($token)
	{
		for($i=0;$i<sizeof(self::$lexical_grammar);$i++)
		{
			if(preg_match("/".self::$lexical_grammar[$i]."/",$token,$match))
			{
				self::$raw_token = $match[0];
				for($j=1;$j<sizeof($match);$j++)
				{
					self::$tokens[] = $match[$j];
				}
				return true;
			}
		}		
	}
	
	private function next_tokens($old_init,$token):String
	{
		$jump = strlen($token);
		$end = strlen(self::$raw_token);
		$start = $jump - $end;
		$new_init = substr_replace($old_init,"",$start,$end);
		
		return $new_init;	
	}
	
	private function token_recognizer($tokens)
	{
		for($i=0;$i<sizeof($tokens);$i++)
		{
			switch($tokens[$i])
			{
				case "function":
					self::KEYWORD($tokens[$i]);
				continue;
				case "(":
				case ")":
				case "{":
				case "}":
					self::SCOPE($tokens[$i]);
				continue;
				default:
					try
					{
						self::is_letter($tokens[$i]);
						self::IDENTIFIER($tokens[$i]);
					}catch(InvalidTokenHandler $error)
					{
						echo $error->getMessage();
					}
				continue;
			}
		}
	}
	
	private function KEYWORD($token)
	{
		echo "KEYWORD(value=>${token})\n";
	}
	
	private function SCOPE($token)
	{
		echo "SCOPE(value=>${token})\n";
	}
		
	private function IDENTIFIER($token)
	{
		echo "IDENTIFIER(value=>${token})\n";	
	}
	
	private function is_letter($token)
	{
		if(ctype_alpha($token) || $token==null)
		{
			return true;
		}else{
			throw new InvalidTokenHandler("\033[1;35m${token}\033[1;31m is an invalid token.\033[m\n");
		}
	}	
}

/*
 * Syntax analyser class for syntax analysing
 * 
 * @comm : will add the some functionalities in later. 
 */ 

class SyntaxAnalyser
{
	static function analyse($source)
	{
		
	}
}

/*
 * @implements : Cores
 * @func       : lexical,syntax
 * @param      : $source
 */ 

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

/*
 * @extends    : Analyser
 * @var        : $source
 * @func       : init,with
 * @param      : $source,$analyser
 */ 

class Compiler extends Analyser
{
	private static $source;

	function init($source):Compiler
	{
		if(file_exists($source))
		{
			$resource = fopen($source,'r');
			self::$source = trim(fread($resource,strlen($source)^1024));
			fclose($resource);
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

/*
 * @extends    : Compiler
 * @var        : $path
 * @func       : run
 * @param      : $argv
 */ 

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
