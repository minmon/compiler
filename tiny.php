<?php

$symbol_table = [
	"syntax"=>[
		"tree" => [
			"name" => [],
			"type" => [],
			"attr" => [
				"operator"=>[],
				"vlateral"=>[],
				"breakern"=>[],
			],						
		],
	],
];

function LexicalAnalyser($source)
{
	return Token($source);
}

function Token($tokens)
{
	$token = explode(" ",$tokens);

	for($i=0;$i<sizeof($token);$i++)
	{
		switch($token[$i])
		{
			case "int":
				$symbol_table["syntax"]["tree"]["type"] = $token[$i];
				$symbol_table["syntax"]["tree"]["name"] = $token[$i+1];
				break;
			case "=":					
				$symbol_table["syntax"]["tree"]["attr"]["operator"] = $token[$i];
				$symbol_table["syntax"]["tree"]["attr"]["vlateral"] = $token[$i+1];						
				break;
			default:					
				$symbol_table["syntax"]["tree"]["attr"]["breakern"] = ";";						
				break;				
		}
	}
	return $symbol_table;
}

function TokenRequest($subject,$token)
{
	if($subject == $token)
	{
		return true;
	}
	
	return null;
}

function SyntaxAnalyser($source)
{	
	$token = LexicalAnalyser($source)["syntax"]["tree"];
			
	$match = explode(" ",$source);
	for($j=0;$j<sizeof($match);$j++)
	{
		switch($match[$j])
		{
			case "=":
				if(TokenRequest(@$match[$j-1],@$token["name"]))
				{
					if(TokenRequest(@$match[$j-2],@$token["type"]))
					{
						if(TokenRequest(@$match[$j+2],@$token["attr"]["breakern"]))
						{
							echo "\033[36mYou have a correct syntax tree.\n";
						}else{
							echo "\033[1;31mYou didn't define the semicolon or space at the end of the line in \033[1;32m${source}^\n";
						}
					}else{
						echo "\033[1;31m${match[$j-1]} is defined in type ${token}[\"type\"].\n
								not in type ${match[$j-2]}\n";
					}
				}else{
					echo "\033[1;31m${match[$j-1]} is not a declared identifier in \033[1;32m^${source}\n";
				}
			break;
		}
	}
}

function Tiny($argv)
{
	$source = __DIR__."/".$argv[1];
	
	if(file_exists($source))
	{
		$resource = fopen($source,'r');
		$source = fread($resource,strlen($source)); 
		SyntaxAnalyser($source);
	}
}

Tiny($argv);
