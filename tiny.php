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

abstract class Analyser implements Cores
{
  static function lexical($source)
  {
    echo $source;
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
