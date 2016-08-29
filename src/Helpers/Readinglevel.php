<?php namespace Hdmaster\Core\Helpers;

class Readinglevel
{

    /**
 * Testmaster Universe
 * 
 * Helper methods that count syllables, words, etc...
 * The main function is reading_level() which puts everything
 * together and returns the flesch-kincaid reading level 
 * 
 * @copyright	Copyright (c) 2011 - Present Headmaster, LLP
 * @link 		http://www.hdmaster.com
 */

    /**
     * Calculates the average words per sentence in a given input string
     * 
     * @param	string	input text / string to calculate words-per-sentence with
     * @return	double
     */
    public static function average_words_sentence($text)
    {
        $sentences = strlen(preg_replace('/[^\.!?]/', '', $text));
        $words = array_filter(explode(//array_filter removes empty elements
                                      ' ', preg_replace('/\s\s+/', ' ', preg_replace('/[\.!?]/', ' ', $text)) //replace double spaces
                                      ));
        //echo "Sentences: ".$sentences."<br />\n";
        //echo "Words: ".count($words)."<br />\n";
        return (count($words)/$sentences);
    }

// --------------------------------------------------------------------------------------------------- 

    /**
     * Calculates and returns the average syllables per word from a given input string
     * 
     * @param	string	input text to calculate syllables of
     * @return	int
     */
    public static function average_syllables_word($text)
    {
        $syllables = 0;
        $words = array_filter(explode(//array_filter removes empty elements
                                      ' ', preg_replace('/\s\s+/', ' ', preg_replace('/[\.!?]/', ' ', $text)) //replace double spaces
                                      ));
        foreach ($words as $word) {
            $syllables = $syllables + self::count_syllables($word);
        }
        //echo "Syllables: ".$syllables."<br />\n";
        return ($syllables/count($words));
    }

// --------------------------------------------------------------------------------------------------- 

    /**
     * Gets the previous character (to specified index $i) in a string $s
     * 
     * @param	string	the input string of all characters
     * @param	int		the index to get previous character of
     * @return	string
     */
    public static function previous($s, $i)
    {
        $result="@";
        if ($i>=0) {
            $result=$s[$i];
        }
        return $result;
    }

// --------------------------------------------------------------------------------------------------- 

    /**
     * Determines whether or not given index of a string is a consonant
     * 
     * @param	string	the string of characters
     * @param	int		the index of a single character
     * @return	boolean
     */
    public static function consonant($s, $i)
    {
        $result=false;
        if ($i>=0) {
            $result = ! (array_search($s[$i], array("~", "A", "E", "I", "O", "U", "Y")));
        }
        return $result;
    }

// --------------------------------------------------------------------------------------------------- 

    /**
     * Counts the syllables in a word
     * 
     * @param	string	the word 
     * @return	int
     */
    public static function count_syllables($word)
    {
        $vowel=false;
        $syllables=0;
        $str=strtoupper($word);
        for ($i = 0; $i < strlen($str); $i++) {
            if (array_search($str[$i], array("`", "A", "E", "I", "O", "U", "Y"))) { //vowel
                if ($i==strlen($str)-1) { //last character
                    if (($str[$i]=="A") && (self::previous($str, $i-1)=="E") && (self::consonant($str, $i-2))) {
                        $syllables++;
                    }
                    if (($str[$i]=="A") && (self::previous($str, $i-1)=="I") && (self::consonant($str, $i-2))) {
                        $syllables++;
                    }
                    if (($str[$i]=="E") && (self::previous($str, $i-1)=="L") && (self::consonant($str, $i-2))) {
                        $syllables++;
                    }
                    if (($str[$i]=="Y") && (self::consonant($str, $i-1))) {
                        $syllables++;
                    }
                }
                if (($str[$i]=="A") && (self::previous($str, $i-1)=="U")) {
                    $syllables++;
                }
                $vowel=true;
            } else { //consonant
                if ($vowel) {
                    if ($i==strlen($str)-1) { //last character
                        if (($str[$i]=="D") && (self::previous($str, $i-1)=="E")) {
                        } else {
                            $syllables++;
                        }
                    } else {
                        $syllables++;
                    }
                }
                $vowel=false;
            }
        }
        $syllables = ($syllables == 0) ? 1 : $syllables;
        //echo $word.":".$syllables."<br />\n";
        return $syllables;
    }

// --------------------------------------------------------------------------------------------------- 

    /**
     * Returns the flesh-kincaid reading level for the input string
     * 
     * @param	string	text to calculate the reading level for
     * @return	double
     */
    public static function calculate_flesch_grade($text)
    {
        return ((.39 * self::average_words_sentence($text)) + (11.8 * self::average_syllables_word($text)) - 15.59);
    }

// --------------------------------------------------------------------------------------------------- 

    /** 
     * Returns flesch-kincaid reading level using the calculate_flesch_grade function
     * 
     * @param	string	text to get reading level of
     * @return	double
     */
    public static function reading_level($text)
    {
        return self::calculate_flesch_grade($text);
    }

// --------------------------------------------------------------------------------------------------- 
}
