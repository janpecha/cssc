<?php
	/**
	 * @author		Jan Pecha, <janpecha@email.cz>
	 * @license		http://janpecha.iunas.cz/cssc/#license
	 * @link		http://janpecha.iunas.cz/cssc/
	 * @version		2012-05-16-1
	 */
	
	namespace Cssc;
	
	class Tokenizer extends \Cz\Tokenizer
	{
		/* Tokens */
		const TOKEN_IDENT = 0,
			TOKEN_ATKEYWORD = 1,
			TOKEN_STRING = 2,
			TOKEN_HASH = 3,
			TOKEN_NUMBER = 4,
			TOKEN_PERCENTAGE = 5,
			TOKEN_DIMENSION = 6,
			TOKEN_URI = 7,
			TOKEN_UNICODE_RANGE = 8,
			TOKEN_CDO = 9,
			TOKEN_CDC = 10,
			TOKEN_S = 11,
			TOKEN_COMMENT = 12,
			TOKEN_FUNCTION = 13,
			TOKEN_INCLUDES = 14,
			TOKEN_DASHMATCH = 15,
			TOKEN_PREFIXMATCH = 16,
			TOKEN_SUFFIXMATCH = 17,
			TOKEN_SUBSTRINGMATCH = 18,
			TOKEN_CHAR = 19,
			TOKEN_BOM = 20;
		
		
		
		public function __construct()
		{
			// matchers[id] = callback((string) input, (int) position, (int) length of input) returns string|FALSE|emptyString
			
			$this->matchers[self::TOKEN_IDENT] = array($this, 'matchIdent');
			$this->matchers[self::TOKEN_ATKEYWORD] = array($this, 'matchAtKeyword');
			$this->matchers[self::TOKEN_STRING] = array($this, 'matchString');
			$this->matchers[self::TOKEN_HASH] = array($this, 'matchHash');
			$this->matchers[self::TOKEN_NUMBER] = array($this, 'matchNumber');
			$this->matchers[self::TOKEN_PERCENTAGE] = array($this, 'matchPercentage');
			$this->matchers[self::TOKEN_DIMENSION] = array($this, 'matchDimension');
			$this->matchers[self::TOKEN_URI] = array($this, 'matchUri');
			$this->matchers[self::TOKEN_UNICODE_RANGE] = array($this, 'matchUnicodeRange');
			$this->matchers[self::TOKEN_S] = array($this, 'matchS');
			$this->matchers[self::TOKEN_COMMENT] = array($this, 'matchComment');
			$this->matchers[self::TOKEN_FUNCTION] = array($this, 'matchFunction');
			
			
			$this->matchers[self::TOKEN_CDO] = '<!--';
			$this->matchers[self::TOKEN_CDC] = '-->';
			$this->matchers[self::TOKEN_INCLUDES] = '~=';
			$this->matchers[self::TOKEN_DASHMATCH] = '|=';
			$this->matchers[self::TOKEN_PREFIXMATCH] = '^=';
			$this->matchers[self::TOKEN_SUFFIXMATCH] = '$=';
			$this->matchers[self::TOKEN_SUBSTRINGMATCH] = '*=';
			
			$this->matchers[self::TOKEN_CHAR] = function ($input, $position, $length) {
				if($input[$position] !== '\'' && $input[$position] !== '"')
				{
					return $input[$position];
				}
				
				return FALSE;
			};
			
			$this->matchers[self::TOKEN_BOM] = "\xEF\xBB\xBF"; // UTF-8 - #xFEFF // TODO: staci?? bug || ficura :)
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchIdent($s, $pos, $len)
		{
			$match = '';
			
			if($s[$pos] === '-')
			{
				$match = $s[$pos];
				$pos++;
			}
			
			$submatch = $this->charNmStart($s, $pos, $len);
			
			if($submatch !== '' && $submatch !== FALSE)
			{
				$match .= $submatch;
				$pos += strlen($submatch);
				
				while($pos < $len)
				{
					$submatch = $this->charNmChar($s, $pos, $len);
		
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
						$pos += strlen($submatch);
					}
					else
					{
						break;
					}
				}
			}
			
			return $match;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchAtKeyword($s, $pos, $len)
		{
			$match = '';
			
			if($s[$pos] === '@')
			{
				$pos++;
				$match = $this->matchIdent($s, $pos, $len);
				
				if($match !== '' && $match !== FALSE)
				{
					return '@' . $match;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchString($s, $pos, $len)
		{
			$enableChar = '';
			$endChar = '';
			$match = '';
			
			if($s[$pos] === '"')
			{
				$enableChar = '\'';
				$endChar = '"';
			}
			elseif($s[$pos] === '\'')
			{
				$enableChar = '"';
				$endChar = '\'';
			}
			else
			{
				return FALSE;
			}
			
			$pos++;
			
			while($pos < $len)
			{
				if($s[$pos] === $enableChar)
				{
					$match .= $enableChar;
					$pos++;
				}
				else
				{
					$submatch = $this->charStringChar($s, $pos, $len);
					
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
						$pos += strlen($submatch);
					}
					else
					{
						if($s[$pos] === $endChar)
						{
							$match .= $endChar;
							$pos++;	// zbytecne
							break;
						}
						else
						{
							return FALSE;
						}
					}
				}
			}
			
			return $match;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchHash($s, $pos, $len)
		{
			$match = FALSE;
			
			if($s[$pos] === '#')
			{
				$match = '#';
				$pos++;
				
				// 'name' match
				while($pos < $len)
				{
					$submatch = $this->charNmChar($s, $pos, $len);
					
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
						$pos += strlen($submatch);
					}
					else
					{
						break;
					}
				}
			}
			
			return $match;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchNumber($s, $pos, $len)
		{
			$match = '';
			$state = 0; /* 0 = invalid, 1 = valid */
			
			while($pos < $len)
			{
				if(self::isDigit($s[$pos]))
				{
					$match .= $s[$pos];
					$pos++;
					$state = 1;
				}
				elseif(($match !== '') && ($s[$pos] === '.'))
				{
					$match .= $s[$pos];
					$pos++;
					$state = 0;
				}
				else
				{
					break;
				}
			}
			
			if($state)
			{
				return $match;
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchPercentage($s, $pos, $len)
		{
			$match = $this->matchNumber($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				$pos += strlen($match);
				
				if($s[$pos] === '%')
				{
					return $match . '%';
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchDimension($s, $pos, $len)
		{
			$match = $this->matchNumber($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				$pos += strlen($match);
				
				$submatch = $this->matchIdent($s, $pos, $len);
				
				if($submatch !== '' && $submatch !== FALSE)
				{
					return $match . $submatch;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchUri($s, $pos, $len)
		{
			$match = '';
			
			if(self::substring($s, $pos, 4) === 'url(')
			{
				// whitespaces
				while($pos < $len)
				{
					$submatch = $this->charWhiteSpace($s, $pos, $len);
					
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
						$pos += strlen($submatch);
					}
					else
					{
						break;
					}
				}
				
				// string | urlchar*
				
				$submatch = $this->matchString($s, $pos, $len);
				
				if($submatch !== '' && $submatch !== FALSE)
				{
					$match .= $submatch;
					$pos += strlen($submatch);
				}
				else	// urlchar *
				{
					$submatch = '';
					
					while($pos < $len)
					{
						$submatch2 = $this->charUrlChar($s, $pos, $len);
						
						if($submatch2 !== '' && $submatch2 !== FALSE)
						{
							$submatch .= $submatch2;
							$pos += strlen($submatch2);
						}
						else
						{
							break;
						}
					}
					
					if($submatch === '')
					{
						return FALSE;
					}
					
					$match .= $submatch;
				}
				
				// whitespaces
				while($pos < $len)
				{
					$submatch = $this->charWhiteSpace($s, $pos, $len);
					
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
						$pos += strlen($submatch);
					}
					else
					{
						break;
					}
				}
				
				if($s[$pos] === ')')
				{
					$match .= $s[$pos];
					
					return $match;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchUnicodeRange($s, $pos, $len)
		{
			$match = '';
			
			if(self::substring($s, $pos, 2) === 'U+')
			{
				$i = 0;
				
				for(; $i < 6; $i++)
				{
					$submatch = $this->charHex($s, $pos, $len);
					
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
						$pos += strlen($submatch);
					}
					else
					{
						if($s[$pos] === '?')
						{
							$match .= $s[$pos];
							$pos++;
						}
					}
				}
				
				if($i === 0)
				{
					return FALSE;
				}
				
				if($s[$pos] === '-')
				{
					$i = 0;
					
					for(; $i < 6; $i++)
					{
						$submatch = $this->charHex($s, $pos, $len);
				
						if($submatch !== '' && $submatch !== FALSE)
						{
							$match .= $submatch;
							$pos += strlen($submatch);
						}
					}
					
					if($i !== 0)
					{
						return $match;
					}
				}
				else
				{
					return $match;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchS($s, $pos, $len)
		{
			$match = '';
			
			while($pos < $len)
			{
				$submatch = $this->charWhiteSpace($s, $pos, $len);
				
				if($submatch !== '' && $submatch !== FALSE)
				{
					$match .= $submatch;
					$pos += strlen($submatch);
				}
				else
				{
					break;
				}
			}
			
			return $match;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchComment($s, $pos, $len)
		{
			// neni podle specifikace (zatim?)
			$match = '';
			
			if(self::substring($s, $pos, 2) === '/*')
			{
				$match = '/*';
				$pos += 2;
				
				while($pos < $len)
				{
					$match .= $s[$pos];
					
					if($s[$pos] === '*')
					{
						$pos++;
						
						if(($pos < $len) && $s[$pos] === '/')
						{
							$match .= '/';
							
							return $match;
						}
						
						$pos--;
					}
					
					$pos++;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function matchFunction($s, $pos, $len)
		{
			$match = $this->matchIdent($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				$pos += strlen($match);
				
				if($s[$pos] === '(')
				{
					return $match . '(';
				}
			}
			
			return FALSE;
		}
		
		
		
		/******* Chars ********************************************************/
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charNmStart($s, $pos, $len)
		{
			if(self::isAlpha($s[$pos]) || ($s[$pos] === '_'))
			{
				return $s[$pos];
			}
			
			$match = $this->charNonAscii($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			$match = $this->charEscape($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charNmChar($s, $pos, $len)
		{
			if(self::isAlphaNum($s[$pos]) || ($s[$pos] === '-') || ($s[$pos] === '_'))
			{
				return $s[$pos];
			}
			
			$match = $this->charNonAscii($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			$match = $this->charEscape($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charStringChar($s, $pos, $len)
		{
			if($s[$pos] === "\x20")
			{
				return $s[$pos];
			}
			
			$match = $this->charUrlChar($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			if($s[$pos] === '\\')
			{
				$match = $s[$pos];
				$pos++;
				
				$submatch = $this->charNewLine($s, $pos, $len);
				
				if($submatch !== '' && $submatch !== FALSE)
				{
					return $match . $submatch;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charUrlChar($s, $pos, $len)
		{
			if($s[$pos] === "\x9" || $s[$pos] === "\x21")
			{
				return $s[$pos];
			}
			
			$code = ord($s[$pos]);
			
			if($code >= 35 && $code <= 126)	// is ok??
			{
				return $s[$pos];
			}
			
			$match = $this->charNonAscii($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			$match = $this->charEscape($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charNewLine($s, $pos, $len)
		{
			if($s[$pos] === "\xA" || $s[$pos] === "\xC")
			{
				return $s[$pos];
			}
			
			if($s[$pos] === "\xD")
			{
				$match = $s[$pos];
				$pos++;
				
				if($s[$pos] === "\xA")
				{
					$match .= $s[$pos];
				}
				
				return $match;
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charEscape($s, $pos, $len)
		{
			$match = $this->charUnicode($s, $pos, $len);
			
			if($match !== '' && $match !== FALSE)
			{
				return $match;
			}
			
			$match = '';
			
			if($s[$pos] === '\\')
			{
				$match = $s[$pos];
				
				// singlebyte
				$code = ord($s[$pos]);
				
				if($code >= 32 && $code <= 126)
				{
					return $match . $s[$pos];
				}
				
				// multibyte
				$submatch = $this->charMultiByteRange($s, $pos, $len);
				
				if($submatch !== '' && $submatch !== FALSE)
				{
					return $match . $submatch;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charUnicode($s, $pos, $len)
		{
			$match = '';
			
			if($s[$pos] === '\\')
			{
				$match = '\\';
				$pos++;
				
				$i = 0;
				
				for(; $i < 6; $i++)
				{
					if(self::isHex($s[$pos]))
					{
						$match .= $s[$pos];
						$pos++;
					}
				}
				
				if($i !== 0)
				{
					$submatch = $this->charWhiteSpace($s, $pos, $len);
					
					if($submatch !== '' && $submatch !== FALSE)
					{
						$match .= $submatch;
					}
					
					return $match;
				}
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charNonAscii($s, $pos, $len)
		{
			return $this->charMultiByteRange($s, $pos, $len);
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charMultiByteRange($s, $pos, $len)
		{
			list($number, $bytes) = $this->utf8ToUnicode($s, $pos, 6);
			
			if(($number >= 0x80 && $number <= 0xD7FF)
			|| ($number >= 0xE000 && $number <= 0xFFFD)
			|| ($number >= 0x10000 && $number <= 0x10FFFF))
			{
				return substr($s, $pos, $bytes);
			}
			
			return FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charWhiteSpace($s, $pos, $len)
		{
			static $whiteSpace = array(
				// #x9 | #xA | #xC | #xD | #x20
				"\x9" => 1,
				"\xA" => 1,
				"\xC" => 1,
				"\xD" => 1,
				"\x20" => 1,
			);
			
			return isset($whiteSpace[$s[$pos]]) ? $s[$pos] : FALSE;
		}
		
		
		
		/**
		 * @param	string	input
		 * @param	int
		 * @param	int		length of input
		 * @return	string|FALSE
		 */
		protected function charHex($s, $pos, $len)
		{
			static $chars = array(
				'0' => 1,
				'1' => 1,
				'2' => 1,
				'3' => 1,
				'4' => 1,
				'5' => 1,
				'6' => 1,
				'7' => 1,
				'8' => 1,
				'9' => 1,
				'A' => 1,
				'B' => 1,
				'C' => 1,
				'D' => 1,
				'E' => 1,
				'F' => 1,
			);
			
			return isset($chars[$s[$pos]]) ? $s[$pos] : FALSE;
		}
		
		
		
		/**
		 * @link	http://randomchaos.com/documents/?source=php_and_unicode
		 * @param	string
		 * @return	int|FALSE 	Unicode number of first UTF-8 char
		 */
		protected function utf8ToUnicode($str, $pos = 0, $len = 6)
		{
		    $values = array();
		    $lookingFor = 1;
		    
		    for($i = $pos; $i < $len; $i++)
		    {
		        $thisValue = ord($str[$i]);
		        
		        if($thisValue < 128)
		        {
		        	return $thisValue;
		        }
		        else
		        {
		            if(count($values) == 0)
		            {
		            	$lookingFor = ($thisValue < 224) ? 2 : 3;
		            }
		            
		            $values[] = $thisValue;
		            
		            if(count($values) == $lookingFor)
		            {
		                $number = ($lookingFor == 3)
		                	? (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64)
		                    : (($values[0] % 32) * 64) + ($values[1] % 64);
		                
		                return array($number, $i - $pos);
		            }
		        }
		    }

		    return FALSE;
		}
	}
	
	
