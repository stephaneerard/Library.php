<?php
namespace se\Library;

class Library
{
	protected $name;
	protected $path;
	
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}
	
	public function getPath()
	{
		return $this->path;
	}
}