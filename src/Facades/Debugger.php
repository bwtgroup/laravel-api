<?php
namespace BwtTeam\LaravelAPI\Facades;

use Illuminate\Support\Facades\Facade;

class Debugger extends Facade {

	protected static function getFacadeAccessor()
	{
		return \BwtTeam\LaravelAPI\Debugger::class;
	}

}