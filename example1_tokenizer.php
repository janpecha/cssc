<?php
	require_once __DIR__ . '/../czproject/Tools/nette.min.php';
	require_once __DIR__ . '/../czproject/Tokenizer/CzTokenizer.php';
	require_once __DIR__ . '/CsscTokenizer.php';

	$tokenizer = new \Cssc\Tokenizer;
	
	dump($tokenizer->tokenize('
		/**
		 * @author Jan Pecha
		 */
		
		body {
			color: #000;
			border: 1px solid red;
			font-size: 120%;
		}
		
		a:hover {
			background: yellow;
		}
		
		b.strong {
			font-weight: bold;
		}
	'));
