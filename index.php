<?php

require('functions.php');

$tarefa = [
	"titulo" => "Angular 2", 
	"descricao" => "Estudar Angular 2.", 
	"responsavel"  => "victor", 
	"completa" => true
];

dd($tarefa);

require "index.view.php";