<?php

namespace ExactMetricsHeadlineToolPlugin;

// setup defines
define ( 'EXACTMETRICS_HEADLINE_TOOL_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Headline Tool
 *
 * @since      0.1
 * @author     Debjit Saha
 */
class ExactMetricsHeadlineToolPlugin{

	/**
	 * Class Variables.
	 */
	private $emotion_power_words2 = array();
	private $power_words = array();
	private $common_words = array();
	private $uncommon_words = array();

	/**
	 * Constructor
	 *
	 * @return   none
	 */
	function __construct() {
		$this->init();

		// Emotion words - 10–15% Density
		$this->emotion_power_words2 = $this->emotion_power_words();

		// Power words - atleast 1
		$this->power_words = $this->power_words();

		// Common words - 20-30% of headline
		$this->common_words = $this->common_words();

		// Un-Common words - 10-20% of headline
		$this->uncommon_words = $this->uncommon_words();
	}

	/**
	 * Add the necessary hooks and filters
	 */
	function init() {
		add_action( 'wp_ajax_exactmetrics_gutenberg_headline_analyzer_get_results', array( $this, 'get_result' ) );
	}

	/**
	 * Ajax request endpoint for the uptime check
	 */
	function get_result() {

		// csrf check
		if ( check_ajax_referer( 'exactmetrics_gutenberg_headline_nonce', false, false ) === false ) {
			$content = self::output_template( 'results-error.php' );
			wp_send_json_error(
				array(
					'html' => $content
				)
			);
		}

  	    // get whether or not the website is up
  	    $result = $this->get_headline_scores();

		if ( !empty( $result->err ) ) {
			$content = self::output_template( 'results-error.php', $result );
			wp_send_json_error(
				array( 'html' => $content, 'analysed' => false )
			);
		} else {

			// send the response
			wp_send_json_success(
					array(
						'result' => $result,
						'analysed' => !$result->err,
						'sentence' => ucwords( wp_unslash( sanitize_text_field( $_REQUEST['q'] ) ) ),
						'score' => ( isset( $result->score ) && ! empty( $result->score ) ) ? $result->score : 0
					)
			);

		}
	}

	/**
	 * function to match words from sentence
	 * @return Object
	 */
	function match_words( $sentence, $sentence_split, $words ) {
		$ret = array();
		foreach( $words as $wrd ) {
			// check if $wrd is a phrase
			if ( strpos( $wrd, ' ' ) !== false ) {
				if ( strpos( $sentence, $wrd ) !== false ) {
					$ret[] = $wrd;
				}
			}
			// if $wrd is a single word
			else {
				if ( in_array( $wrd, $sentence_split ) ) {
					$ret[] = $wrd;
				}
			}
		}
		return $ret;
	}

	/**
	 * main function to calculate headline scores
	 * @return Object
	 */
	function get_headline_scores() {
		$input = sanitize_text_field( @$_REQUEST['q'] );

		// init the result array
		$result = new \stdClass();
		$result->input_array_orig = explode( ' ', wp_unslash( $input ) );

		// strip useless characters
		$input = preg_replace( '/[^A-Za-z0-9 ]/', '', $input );

		// strip whitespace
		$input = preg_replace( '!\s+!', ' ', $input);

		// lower case
		$input = strtolower( $input );

		$result->input = $input;

  	    // bad input
		if ( ! $input || $input == ' ' || trim( $input ) == '' ) {
				$result->err = true;
				$result->msg = __('Bad Input', 'google-analytics-dashboard-for-wp');
				return $result;
		}

		// overall score;
		$scoret = 0;

		// headline array
		$input_array = explode( ' ', $input );

		$result->input_array = $input_array;

		// all okay, start analysis
		$result->err = false;

		// Length - 55 chars. optimal
		$result->length = strlen( str_replace( ' ', '', $input ) );
		$scoret = $scoret + 3;

		if ( $result->length <= 19 ) { $scoret += 5; }
		elseif ( $result->length >= 20 && $result->length <= 34 ) { $scoret += 8; }
		elseif ( $result->length >= 35 && $result->length <= 66 ) { $scoret += 11; }
		elseif ( $result->length >= 67 && $result->length <= 79 ) { $scoret += 8; }
		elseif ( $result->length >= 80 ) { $scoret += 5; }

		// Count - typically 6-7 words
		$result->word_count = count( $input_array );
		$scoret = $scoret + 3;

		if ( $result->word_count == 0 ) { $scoret = 0; }
		else if ( $result->word_count >= 2 && $result->word_count <= 4 ) { $scoret += 5; }
		elseif ( $result->word_count >= 5 && $result->word_count <= 9 ) { $scoret += 11; }
		elseif ( $result->word_count >= 10 && $result->word_count <= 11 ) { $scoret += 8; }
		elseif ( $result->word_count >= 12 ) { $scoret += 5; }

		// Calculate word match counts
		$result->power_words = $this->match_words( $result->input, $result->input_array, $this->power_words );
		$result->power_words_per = count( $result->power_words ) / $result->word_count;
		$result->emotion_words = $this->match_words( $result->input, $result->input_array, $this->emotion_power_words2 );
		$result->emotion_words_per = count( $result->emotion_words ) / $result->word_count;
		$result->common_words = $this->match_words( $result->input, $result->input_array, $this->common_words );
		$result->common_words_per = count( $result->common_words ) / $result->word_count;
		$result->uncommon_words = $this->match_words( $result->input, $result->input_array, $this->uncommon_words );
		$result->uncommon_words_per = count( $result->uncommon_words ) / $result->word_count;
		$result->word_balance = __('Can Be Improved', 'google-analytics-dashboard-for-wp');
		$result->word_balance_use = array();

		if ( $result->emotion_words_per < 0.1 ) {
			$result->word_balance_use[] = __('emotion', 'google-analytics-dashboard-for-wp');
		} else {
			$scoret = $scoret + 15;
		}

		if ( $result->common_words_per < 0.2 ) {
			$result->word_balance_use[] = __('common', 'google-analytics-dashboard-for-wp');
		} else {
			$scoret = $scoret + 11;
		}

		if ( $result->uncommon_words_per < 0.1 ) {
			$result->word_balance_use[] = __('uncommon', 'google-analytics-dashboard-for-wp');
		} else
			$scoret = $scoret + 15;

		if ( count( $result->power_words ) < 1 ) {
			$result->word_balance_use[] = __('power', 'google-analytics-dashboard-for-wp');
		} else {
			$scoret = $scoret + 19;
		}

		if (
			$result->emotion_words_per >= 0.1 &&
			$result->common_words_per >= 0.2 &&
			$result->uncommon_words_per >= 0.1 &&
			count( $result->power_words ) >= 1 ) {
			$result->word_balance = __('Perfect', 'google-analytics-dashboard-for-wp');
			$scoret = $scoret + 3;
		}

		// Sentiment analysis also look - https://github.com/yooper/php-text-analysis

		// Emotion of the headline - sentiment analysis
		// Credits - https://github.com/JWHennessey/phpInsight/
		require_once EXACTMETRICS_HEADLINE_TOOL_DIR_PATH . '/phpinsight/autoload.php';
		$sentiment = new \PHPInsight\Sentiment();
		$class_senti = $sentiment->categorise( $input );
		$result->sentiment = $class_senti;

		$scoret = $scoret + ( $result->sentiment == 'pos' ? 10 : ( $result->sentiment == 'neg' ? 10 : 7 ) );

		// Headline types
		$headline_types = array();

		// HDL type: how to, how-to, howto
		if ( strpos( $input, __('how to', 'google-analytics-dashboard-for-wp') ) !== false || strpos( $input, __('howto', 'google-analytics-dashboard-for-wp') ) !== false ) {
			$headline_types[] = __('How-To', 'google-analytics-dashboard-for-wp');
			$scoret = $scoret + 7;
		}

		// HDL type: numbers - numeric and alpha
		$num_quantifiers = array(
			__('one', 'google-analytics-dashboard-for-wp'),
			__('two', 'google-analytics-dashboard-for-wp'),
			__('three', 'google-analytics-dashboard-for-wp'),
			__('four', 'google-analytics-dashboard-for-wp'),
			__('five', 'google-analytics-dashboard-for-wp'),
			__('six', 'google-analytics-dashboard-for-wp'),
			__('seven', 'google-analytics-dashboard-for-wp'),
			__('eight', 'google-analytics-dashboard-for-wp'),
			__('nine', 'google-analytics-dashboard-for-wp'),
			__('eleven', 'google-analytics-dashboard-for-wp'),
			__('twelve', 'google-analytics-dashboard-for-wp'),
			__('thirt', 'google-analytics-dashboard-for-wp'),
			__('fift', 'google-analytics-dashboard-for-wp'),
			__('hundred', 'google-analytics-dashboard-for-wp'),
			__('thousand', 'google-analytics-dashboard-for-wp'),
		);

		$list_words = array_intersect( $input_array, $num_quantifiers );
		if ( preg_match( '~[0-9]+~', $input ) || ! empty ( $list_words ) ) {
			$headline_types[] = __('List', 'google-analytics-dashboard-for-wp');
			$scoret = $scoret + 7;
		}

		// HDL type: Question
		$qn_quantifiers = array(
			__('where', 'google-analytics-dashboard-for-wp'),
			__('when', 'google-analytics-dashboard-for-wp'),
			__('how', 'google-analytics-dashboard-for-wp'),
			__('what', 'google-analytics-dashboard-for-wp'),
			__('have', 'google-analytics-dashboard-for-wp'),
			__('has', 'google-analytics-dashboard-for-wp'),
			__('does', 'google-analytics-dashboard-for-wp'),
			__('do', 'google-analytics-dashboard-for-wp'),
			__('can', 'google-analytics-dashboard-for-wp'),
			__('are', 'google-analytics-dashboard-for-wp'),
			__('will', 'google-analytics-dashboard-for-wp'),
		);
		$qn_quantifiers_sub = array(
			__('you', 'google-analytics-dashboard-for-wp'),
			__('they', 'google-analytics-dashboard-for-wp'),
			__('he', 'google-analytics-dashboard-for-wp'),
			__('she', 'google-analytics-dashboard-for-wp'),
			__('your', 'google-analytics-dashboard-for-wp'),
			__('it', 'google-analytics-dashboard-for-wp'),
			__('they', 'google-analytics-dashboard-for-wp'),
			__('my', 'google-analytics-dashboard-for-wp'),
			__('have', 'google-analytics-dashboard-for-wp'),
			__('has', 'google-analytics-dashboard-for-wp'),
			__('does', 'google-analytics-dashboard-for-wp'),
			__('do', 'google-analytics-dashboard-for-wp'),
			__('can', 'google-analytics-dashboard-for-wp'),
			__('are', 'google-analytics-dashboard-for-wp'),
			__('will', 'google-analytics-dashboard-for-wp'),
		);
		if ( in_array( $input_array[0], $qn_quantifiers ) ) {
			if ( in_array( $input_array[1], $qn_quantifiers_sub ) ) {
				$headline_types[] = __('Question', 'google-analytics-dashboard-for-wp');
				$scoret = $scoret + 7;
			}
		}

		// General headline type
		if ( empty( $headline_types ) ) {
			$headline_types[] = __('General', 'google-analytics-dashboard-for-wp');
			$scoret = $scoret + 5;
		}

		// put to result
		$result->headline_types = $headline_types;

        // Resources for more reading:
		// https://kopywritingkourse.com/copywriting-headlines-that-sell/
		// How To _______ That Will Help You ______
		// https://coschedule.com/blog/how-to-write-the-best-headlines-that-will-increase-traffic/

		$result->score = $scoret >= 93 ? 93 : $scoret;

		return $result;
	}

	/**
	 * Output template contents
	 * @param $template String template file name
	 * @return String template content
	 */
	static function output_template( $template, $result = '', $theme = '' ) {
		ob_start();
		require EXACTMETRICS_HEADLINE_TOOL_DIR_PATH . '' . $template;
		$tmp = ob_get_contents();
		ob_end_clean();
		return $tmp;
	}

	/**
	 * Get User IP
	 *
	 * Returns the IP address of the current visitor
	 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/blob/904db487f6c07a3a46903202d31d4e8ea2b30808/includes/misc-functions.php#L163
	 * @return string $ip User's IP address
	 */
	static function get_ip() {

		$ip = '127.0.0.1';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// Fix potential CSV returned from $_SERVER variables
		$ip_array = explode( ',', $ip );
		$ip_array = array_map( 'trim', $ip_array );

		return $ip_array[0];
	}

	/**
	 * Emotional power words
	 *
	 * @return array emotional power words
	 */
	function emotion_power_words() {
		return array(
			__("destroy", "google-analytics-dashboard-for-wp"),
			__("extra", "google-analytics-dashboard-for-wp"),
			__("in a", "google-analytics-dashboard-for-wp"),
			__("devastating", "google-analytics-dashboard-for-wp"),
			__("eye-opening", "google-analytics-dashboard-for-wp"),
			__("gift", "google-analytics-dashboard-for-wp"),
			__("in the world", "google-analytics-dashboard-for-wp"),
			__("devoted", "google-analytics-dashboard-for-wp"),
			__("fail", "google-analytics-dashboard-for-wp"),
			__("in the", "google-analytics-dashboard-for-wp"),
			__("faith", "google-analytics-dashboard-for-wp"),
			__("grateful", "google-analytics-dashboard-for-wp"),
			__("inexpensive", "google-analytics-dashboard-for-wp"),
			__("dirty", "google-analytics-dashboard-for-wp"),
			__("famous", "google-analytics-dashboard-for-wp"),
			__("disastrous", "google-analytics-dashboard-for-wp"),
			__("fantastic", "google-analytics-dashboard-for-wp"),
			__("greed", "google-analytics-dashboard-for-wp"),
			__("grit", "google-analytics-dashboard-for-wp"),
			__("insanely", "google-analytics-dashboard-for-wp"),
			__("disgusting", "google-analytics-dashboard-for-wp"),
			__("fearless", "google-analytics-dashboard-for-wp"),
			__("disinformation", "google-analytics-dashboard-for-wp"),
			__("feast", "google-analytics-dashboard-for-wp"),
			__("insidious", "google-analytics-dashboard-for-wp"),
			__("dollar", "google-analytics-dashboard-for-wp"),
			__("feeble", "google-analytics-dashboard-for-wp"),
			__("gullible", "google-analytics-dashboard-for-wp"),
			__("double", "google-analytics-dashboard-for-wp"),
			__("ﬁre", "google-analytics-dashboard-for-wp"),
			__("hack", "google-analytics-dashboard-for-wp"),
			__("ﬂeece", "google-analytics-dashboard-for-wp"),
			__("had enough", "google-analytics-dashboard-for-wp"),
			__("invasion", "google-analytics-dashboard-for-wp"),
			__("drowning", "google-analytics-dashboard-for-wp"),
			__("ﬂoundering", "google-analytics-dashboard-for-wp"),
			__("happy", "google-analytics-dashboard-for-wp"),
			__("ironclad", "google-analytics-dashboard-for-wp"),
			__("dumb", "google-analytics-dashboard-for-wp"),
			__("ﬂush", "google-analytics-dashboard-for-wp"),
			__("hate", "google-analytics-dashboard-for-wp"),
			__("irresistibly", "google-analytics-dashboard-for-wp"),
			__("hazardous", "google-analytics-dashboard-for-wp"),
			__("is the", "google-analytics-dashboard-for-wp"),
			__("fool", "google-analytics-dashboard-for-wp"),
			__("is what happens when", "google-analytics-dashboard-for-wp"),
			__("fooled", "google-analytics-dashboard-for-wp"),
			__("helpless", "google-analytics-dashboard-for-wp"),
			__("it looks like a", "google-analytics-dashboard-for-wp"),
			__("embarrass", "google-analytics-dashboard-for-wp"),
			__("for the ﬁrst time", "google-analytics-dashboard-for-wp"),
			__("help are the", "google-analytics-dashboard-for-wp"),
			__("jackpot", "google-analytics-dashboard-for-wp"),
			__("forbidden", "google-analytics-dashboard-for-wp"),
			__("hidden", "google-analytics-dashboard-for-wp"),
			__("jail", "google-analytics-dashboard-for-wp"),
			__("empower", "google-analytics-dashboard-for-wp"),
			__("force-fed", "google-analytics-dashboard-for-wp"),
			__("high", "google-analytics-dashboard-for-wp"),
			__("jaw-dropping", "google-analytics-dashboard-for-wp"),
			__("forgotten", "google-analytics-dashboard-for-wp"),
			__("jeopardy", "google-analytics-dashboard-for-wp"),
			__("energize", "google-analytics-dashboard-for-wp"),
			__("hoax", "google-analytics-dashboard-for-wp"),
			__("jubilant", "google-analytics-dashboard-for-wp"),
			__("foul", "google-analytics-dashboard-for-wp"),
			__("hope", "google-analytics-dashboard-for-wp"),
			__("killer", "google-analytics-dashboard-for-wp"),
			__("frantic", "google-analytics-dashboard-for-wp"),
			__("horriﬁc", "google-analytics-dashboard-for-wp"),
			__("know it all", "google-analytics-dashboard-for-wp"),
			__("epic", "google-analytics-dashboard-for-wp"),
			__("how to make", "google-analytics-dashboard-for-wp"),
			__("evil", "google-analytics-dashboard-for-wp"),
			__("freebie", "google-analytics-dashboard-for-wp"),
			__("frenzy", "google-analytics-dashboard-for-wp"),
			__("hurricane", "google-analytics-dashboard-for-wp"),
			__("excited", "google-analytics-dashboard-for-wp"),
			__("fresh on the mind", "google-analytics-dashboard-for-wp"),
			__("frightening", "google-analytics-dashboard-for-wp"),
			__("hypnotic", "google-analytics-dashboard-for-wp"),
			__("lawsuit", "google-analytics-dashboard-for-wp"),
			__("frugal", "google-analytics-dashboard-for-wp"),
			__("illegal", "google-analytics-dashboard-for-wp"),
			__("fulﬁll", "google-analytics-dashboard-for-wp"),
			__("lick", "google-analytics-dashboard-for-wp"),
			__("explode", "google-analytics-dashboard-for-wp"),
			__("lies", "google-analytics-dashboard-for-wp"),
			__("exposed", "google-analytics-dashboard-for-wp"),
			__("gambling", "google-analytics-dashboard-for-wp"),
			__("like a normal", "google-analytics-dashboard-for-wp"),
			__("nightmare", "google-analytics-dashboard-for-wp"),
			__("results", "google-analytics-dashboard-for-wp"),
			__("line", "google-analytics-dashboard-for-wp"),
			__("no good", "google-analytics-dashboard-for-wp"),
			__("pound", "google-analytics-dashboard-for-wp"),
			__("loathsome", "google-analytics-dashboard-for-wp"),
			__("no questions asked", "google-analytics-dashboard-for-wp"),
			__("revenge", "google-analytics-dashboard-for-wp"),
			__("lonely", "google-analytics-dashboard-for-wp"),
			__("looks like a", "google-analytics-dashboard-for-wp"),
			__("obnoxious", "google-analytics-dashboard-for-wp"),
			__("preposterous", "google-analytics-dashboard-for-wp"),
			__("revolting", "google-analytics-dashboard-for-wp"),
			__("looming", "google-analytics-dashboard-for-wp"),
			__("priced", "google-analytics-dashboard-for-wp"),
			__("lost", "google-analytics-dashboard-for-wp"),
			__("prison", "google-analytics-dashboard-for-wp"),
			__("lowest", "google-analytics-dashboard-for-wp"),
			__("of the", "google-analytics-dashboard-for-wp"),
			__("privacy", "google-analytics-dashboard-for-wp"),
			__("rich", "google-analytics-dashboard-for-wp"),
			__("lunatic", "google-analytics-dashboard-for-wp"),
			__("off-limits", "google-analytics-dashboard-for-wp"),
			__("private", "google-analytics-dashboard-for-wp"),
			__("risky", "google-analytics-dashboard-for-wp"),
			__("lurking", "google-analytics-dashboard-for-wp"),
			__("offer", "google-analytics-dashboard-for-wp"),
			__("prize", "google-analytics-dashboard-for-wp"),
			__("ruthless", "google-analytics-dashboard-for-wp"),
			__("lust", "google-analytics-dashboard-for-wp"),
			__("official", "google-analytics-dashboard-for-wp"),
			__("luxurious", "google-analytics-dashboard-for-wp"),
			__("on the", "google-analytics-dashboard-for-wp"),
			__("proﬁt", "google-analytics-dashboard-for-wp"),
			__("scary", "google-analytics-dashboard-for-wp"),
			__("lying", "google-analytics-dashboard-for-wp"),
			__("outlawed", "google-analytics-dashboard-for-wp"),
			__("protected", "google-analytics-dashboard-for-wp"),
			__("scream", "google-analytics-dashboard-for-wp"),
			__("searing", "google-analytics-dashboard-for-wp"),
			__("overcome", "google-analytics-dashboard-for-wp"),
			__("provocative", "google-analytics-dashboard-for-wp"),
			__("make you", "google-analytics-dashboard-for-wp"),
			__("painful", "google-analytics-dashboard-for-wp"),
			__("pummel", "google-analytics-dashboard-for-wp"),
			__("secure", "google-analytics-dashboard-for-wp"),
			__("pale", "google-analytics-dashboard-for-wp"),
			__("punish", "google-analytics-dashboard-for-wp"),
			__("marked down", "google-analytics-dashboard-for-wp"),
			__("panic", "google-analytics-dashboard-for-wp"),
			__("quadruple", "google-analytics-dashboard-for-wp"),
			__("secutively", "google-analytics-dashboard-for-wp"),
			__("massive", "google-analytics-dashboard-for-wp"),
			__("pay zero", "google-analytics-dashboard-for-wp"),
			__("seize", "google-analytics-dashboard-for-wp"),
			__("meltdown", "google-analytics-dashboard-for-wp"),
			__("payback", "google-analytics-dashboard-for-wp"),
			__("might look like a", "google-analytics-dashboard-for-wp"),
			__("peril", "google-analytics-dashboard-for-wp"),
			__("mind-blowing", "google-analytics-dashboard-for-wp"),
			__("shameless", "google-analytics-dashboard-for-wp"),
			__("minute", "google-analytics-dashboard-for-wp"),
			__("rave", "google-analytics-dashboard-for-wp"),
			__("shatter", "google-analytics-dashboard-for-wp"),
			__("piranha", "google-analytics-dashboard-for-wp"),
			__("reckoning", "google-analytics-dashboard-for-wp"),
			__("shellacking", "google-analytics-dashboard-for-wp"),
			__("mired", "google-analytics-dashboard-for-wp"),
			__("pitfall", "google-analytics-dashboard-for-wp"),
			__("reclaim", "google-analytics-dashboard-for-wp"),
			__("mistakes", "google-analytics-dashboard-for-wp"),
			__("plague", "google-analytics-dashboard-for-wp"),
			__("sick and tired", "google-analytics-dashboard-for-wp"),
			__("money", "google-analytics-dashboard-for-wp"),
			__("played", "google-analytics-dashboard-for-wp"),
			__("refugee", "google-analytics-dashboard-for-wp"),
			__("silly", "google-analytics-dashboard-for-wp"),
			__("money-grubbing", "google-analytics-dashboard-for-wp"),
			__("pluck", "google-analytics-dashboard-for-wp"),
			__("refund", "google-analytics-dashboard-for-wp"),
			__("moneyback", "google-analytics-dashboard-for-wp"),
			__("plummet", "google-analytics-dashboard-for-wp"),
			__("plunge", "google-analytics-dashboard-for-wp"),
			__("murder", "google-analytics-dashboard-for-wp"),
			__("pointless", "google-analytics-dashboard-for-wp"),
			__("sinful", "google-analytics-dashboard-for-wp"),
			__("myths", "google-analytics-dashboard-for-wp"),
			__("poor", "google-analytics-dashboard-for-wp"),
			__("remarkably", "google-analytics-dashboard-for-wp"),
			__("six-ﬁgure", "google-analytics-dashboard-for-wp"),
			__("never again", "google-analytics-dashboard-for-wp"),
			__("research", "google-analytics-dashboard-for-wp"),
			__("surrender", "google-analytics-dashboard-for-wp"),
			__("to the", "google-analytics-dashboard-for-wp"),
			__("varify", "google-analytics-dashboard-for-wp"),
			__("skyrocket", "google-analytics-dashboard-for-wp"),
			__("toxic", "google-analytics-dashboard-for-wp"),
			__("vibrant", "google-analytics-dashboard-for-wp"),
			__("slaughter", "google-analytics-dashboard-for-wp"),
			__("swindle", "google-analytics-dashboard-for-wp"),
			__("trap", "google-analytics-dashboard-for-wp"),
			__("victim", "google-analytics-dashboard-for-wp"),
			__("sleazy", "google-analytics-dashboard-for-wp"),
			__("taboo", "google-analytics-dashboard-for-wp"),
			__("treasure", "google-analytics-dashboard-for-wp"),
			__("victory", "google-analytics-dashboard-for-wp"),
			__("smash", "google-analytics-dashboard-for-wp"),
			__("tailspin", "google-analytics-dashboard-for-wp"),
			__("vindication", "google-analytics-dashboard-for-wp"),
			__("smug", "google-analytics-dashboard-for-wp"),
			__("tank", "google-analytics-dashboard-for-wp"),
			__("triple", "google-analytics-dashboard-for-wp"),
			__("viral", "google-analytics-dashboard-for-wp"),
			__("smuggled", "google-analytics-dashboard-for-wp"),
			__("tantalizing", "google-analytics-dashboard-for-wp"),
			__("triumph", "google-analytics-dashboard-for-wp"),
			__("volatile", "google-analytics-dashboard-for-wp"),
			__("sniveling", "google-analytics-dashboard-for-wp"),
			__("targeted", "google-analytics-dashboard-for-wp"),
			__("truth", "google-analytics-dashboard-for-wp"),
			__("vulnerable", "google-analytics-dashboard-for-wp"),
			__("snob", "google-analytics-dashboard-for-wp"),
			__("tawdry", "google-analytics-dashboard-for-wp"),
			__("try before you buy", "google-analytics-dashboard-for-wp"),
			__("tech", "google-analytics-dashboard-for-wp"),
			__("turn the tables", "google-analytics-dashboard-for-wp"),
			__("wanton", "google-analytics-dashboard-for-wp"),
			__("soaring", "google-analytics-dashboard-for-wp"),
			__("warning", "google-analytics-dashboard-for-wp"),
			__("teetering", "google-analytics-dashboard-for-wp"),
			__("unauthorized", "google-analytics-dashboard-for-wp"),
			__("spectacular", "google-analytics-dashboard-for-wp"),
			__("temporary ﬁx", "google-analytics-dashboard-for-wp"),
			__("unbelievably", "google-analytics-dashboard-for-wp"),
			__("spine", "google-analytics-dashboard-for-wp"),
			__("tempting", "google-analytics-dashboard-for-wp"),
			__("uncommonly", "google-analytics-dashboard-for-wp"),
			__("what happened", "google-analytics-dashboard-for-wp"),
			__("spirit", "google-analytics-dashboard-for-wp"),
			__("what happens when", "google-analytics-dashboard-for-wp"),
			__("terror", "google-analytics-dashboard-for-wp"),
			__("under", "google-analytics-dashboard-for-wp"),
			__("what happens", "google-analytics-dashboard-for-wp"),
			__("staggering", "google-analytics-dashboard-for-wp"),
			__("underhanded", "google-analytics-dashboard-for-wp"),
			__("what this", "google-analytics-dashboard-for-wp"),
			__("that will make you", "google-analytics-dashboard-for-wp"),
			__("undo","when you see", "google-analytics-dashboard-for-wp"),
			__("that will make", "google-analytics-dashboard-for-wp"),
			__("unexpected", "google-analytics-dashboard-for-wp"),
			__("when you", "google-analytics-dashboard-for-wp"),
			__("strangle", "google-analytics-dashboard-for-wp"),
			__("that will", "google-analytics-dashboard-for-wp"),
			__("whip", "google-analytics-dashboard-for-wp"),
			__("the best", "google-analytics-dashboard-for-wp"),
			__("whopping", "google-analytics-dashboard-for-wp"),
			__("stuck up", "google-analytics-dashboard-for-wp"),
			__("the ranking of", "google-analytics-dashboard-for-wp"),
			__("wicked", "google-analytics-dashboard-for-wp"),
			__("stunning", "google-analytics-dashboard-for-wp"),
			__("the most", "google-analytics-dashboard-for-wp"),
			__("will make you", "google-analytics-dashboard-for-wp"),
			__("stupid", "google-analytics-dashboard-for-wp"),
			__("the reason why is", "google-analytics-dashboard-for-wp"),
			__("unscrupulous", "google-analytics-dashboard-for-wp"),
			__("thing ive ever seen", "google-analytics-dashboard-for-wp"),
			__("withheld", "google-analytics-dashboard-for-wp"),
			__("this is the", "google-analytics-dashboard-for-wp"),
			__("this is what happens", "google-analytics-dashboard-for-wp"),
			__("unusually", "google-analytics-dashboard-for-wp"),
			__("wondrous", "google-analytics-dashboard-for-wp"),
			__("this is what", "google-analytics-dashboard-for-wp"),
			__("uplifting", "google-analytics-dashboard-for-wp"),
			__("worry", "google-analytics-dashboard-for-wp"),
			__("sure", "google-analytics-dashboard-for-wp"),
			__("this is", "google-analytics-dashboard-for-wp"),
			__("wounded", "google-analytics-dashboard-for-wp"),
			__("surge", "google-analytics-dashboard-for-wp"),
			__("thrilled", "google-analytics-dashboard-for-wp"),
			__("you need to know", "google-analytics-dashboard-for-wp"),
			__("thrilling", "google-analytics-dashboard-for-wp"),
			__("valor", "google-analytics-dashboard-for-wp"),
			__("you need to", "google-analytics-dashboard-for-wp"),
			__("you see what", "google-analytics-dashboard-for-wp"),
			__("surprising", "google-analytics-dashboard-for-wp"),
			__("tired", "google-analytics-dashboard-for-wp"),
			__("you see", "google-analytics-dashboard-for-wp"),
			__("surprisingly", "google-analytics-dashboard-for-wp"),
			__("to be", "google-analytics-dashboard-for-wp"),
			__("vaporize", "google-analytics-dashboard-for-wp"),
		);
	}

	/**
	 * Power words
	 *
	 * @return array power words
	 */
	function power_words() {
		return array(
			__("great", "google-analytics-dashboard-for-wp"),
			__("free", "google-analytics-dashboard-for-wp"),
			__("focus", "google-analytics-dashboard-for-wp"),
			__("remarkable", "google-analytics-dashboard-for-wp"),
			__("conﬁdential", "google-analytics-dashboard-for-wp"),
			__("sale", "google-analytics-dashboard-for-wp"),
			__("wanted", "google-analytics-dashboard-for-wp"),
			__("obsession", "google-analytics-dashboard-for-wp"),
			__("sizable", "google-analytics-dashboard-for-wp"),
			__("new", "google-analytics-dashboard-for-wp"),
			__("absolutely lowest", "google-analytics-dashboard-for-wp"),
			__("surging", "google-analytics-dashboard-for-wp"),
			__("wonderful", "google-analytics-dashboard-for-wp"),
			__("professional", "google-analytics-dashboard-for-wp"),
			__("interesting", "google-analytics-dashboard-for-wp"),
			__("revisited", "google-analytics-dashboard-for-wp"),
			__("delivered", "google-analytics-dashboard-for-wp"),
			__("guaranteed", "google-analytics-dashboard-for-wp"),
			__("challenge", "google-analytics-dashboard-for-wp"),
			__("unique", "google-analytics-dashboard-for-wp"),
			__("secrets", "google-analytics-dashboard-for-wp"),
			__("special", "google-analytics-dashboard-for-wp"),
			__("lifetime", "google-analytics-dashboard-for-wp"),
			__("bargain", "google-analytics-dashboard-for-wp"),
			__("scarce", "google-analytics-dashboard-for-wp"),
			__("tested", "google-analytics-dashboard-for-wp"),
			__("highest", "google-analytics-dashboard-for-wp"),
			__("hurry", "google-analytics-dashboard-for-wp"),
			__("alert famous", "google-analytics-dashboard-for-wp"),
			__("improved", "google-analytics-dashboard-for-wp"),
			__("expert", "google-analytics-dashboard-for-wp"),
			__("daring", "google-analytics-dashboard-for-wp"),
			__("strong", "google-analytics-dashboard-for-wp"),
			__("immediately", "google-analytics-dashboard-for-wp"),
			__("advice", "google-analytics-dashboard-for-wp"),
			__("pioneering", "google-analytics-dashboard-for-wp"),
			__("unusual", "google-analytics-dashboard-for-wp"),
			__("limited", "google-analytics-dashboard-for-wp"),
			__("the truth about", "google-analytics-dashboard-for-wp"),
			__("destiny", "google-analytics-dashboard-for-wp"),
			__("outstanding", "google-analytics-dashboard-for-wp"),
			__("simplistic", "google-analytics-dashboard-for-wp"),
			__("compare", "google-analytics-dashboard-for-wp"),
			__("unsurpassed", "google-analytics-dashboard-for-wp"),
			__("energy", "google-analytics-dashboard-for-wp"),
			__("powerful", "google-analytics-dashboard-for-wp"),
			__("colorful", "google-analytics-dashboard-for-wp"),
			__("genuine", "google-analytics-dashboard-for-wp"),
			__("instructive", "google-analytics-dashboard-for-wp"),
			__("big", "google-analytics-dashboard-for-wp"),
			__("affordable", "google-analytics-dashboard-for-wp"),
			__("informative", "google-analytics-dashboard-for-wp"),
			__("liberal", "google-analytics-dashboard-for-wp"),
			__("popular", "google-analytics-dashboard-for-wp"),
			__("ultimate", "google-analytics-dashboard-for-wp"),
			__("mainstream", "google-analytics-dashboard-for-wp"),
			__("rare", "google-analytics-dashboard-for-wp"),
			__("exclusive", "google-analytics-dashboard-for-wp"),
			__("willpower", "google-analytics-dashboard-for-wp"),
			__("complete", "google-analytics-dashboard-for-wp"),
			__("edge", "google-analytics-dashboard-for-wp"),
			__("valuable", "google-analytics-dashboard-for-wp"),
			__("attractive", "google-analytics-dashboard-for-wp"),
			__("last chance", "google-analytics-dashboard-for-wp"),
			__("superior", "google-analytics-dashboard-for-wp"),
			__("how to", "google-analytics-dashboard-for-wp"),
			__("easily", "google-analytics-dashboard-for-wp"),
			__("exploit", "google-analytics-dashboard-for-wp"),
			__("unparalleled", "google-analytics-dashboard-for-wp"),
			__("endorsed", "google-analytics-dashboard-for-wp"),
			__("approved", "google-analytics-dashboard-for-wp"),
			__("quality", "google-analytics-dashboard-for-wp"),
			__("fascinating", "google-analytics-dashboard-for-wp"),
			__("unlimited", "google-analytics-dashboard-for-wp"),
			__("competitive", "google-analytics-dashboard-for-wp"),
			__("gigantic", "google-analytics-dashboard-for-wp"),
			__("compromise", "google-analytics-dashboard-for-wp"),
			__("discount", "google-analytics-dashboard-for-wp"),
			__("full", "google-analytics-dashboard-for-wp"),
			__("love", "google-analytics-dashboard-for-wp"),
			__("odd", "google-analytics-dashboard-for-wp"),
			__("fundamentals", "google-analytics-dashboard-for-wp"),
			__("mammoth", "google-analytics-dashboard-for-wp"),
			__("lavishly", "google-analytics-dashboard-for-wp"),
			__("bottom line", "google-analytics-dashboard-for-wp"),
			__("under priced", "google-analytics-dashboard-for-wp"),
			__("innovative", "google-analytics-dashboard-for-wp"),
			__("reliable", "google-analytics-dashboard-for-wp"),
			__("zinger", "google-analytics-dashboard-for-wp"),
			__("suddenly", "google-analytics-dashboard-for-wp"),
			__("it's here", "google-analytics-dashboard-for-wp"),
			__("terriﬁc", "google-analytics-dashboard-for-wp"),
			__("simpliﬁed", "google-analytics-dashboard-for-wp"),
			__("perspective", "google-analytics-dashboard-for-wp"),
			__("just arrived", "google-analytics-dashboard-for-wp"),
			__("breakthrough", "google-analytics-dashboard-for-wp"),
			__("tremendous", "google-analytics-dashboard-for-wp"),
			__("launching", "google-analytics-dashboard-for-wp"),
			__("sure ﬁre", "google-analytics-dashboard-for-wp"),
			__("emerging", "google-analytics-dashboard-for-wp"),
			__("helpful", "google-analytics-dashboard-for-wp"),
			__("skill", "google-analytics-dashboard-for-wp"),
			__("soar", "google-analytics-dashboard-for-wp"),
			__("proﬁtable", "google-analytics-dashboard-for-wp"),
			__("special offer", "google-analytics-dashboard-for-wp"),
			__("reduced", "google-analytics-dashboard-for-wp"),
			__("beautiful", "google-analytics-dashboard-for-wp"),
			__("sampler", "google-analytics-dashboard-for-wp"),
			__("technology", "google-analytics-dashboard-for-wp"),
			__("better", "google-analytics-dashboard-for-wp"),
			__("crammed", "google-analytics-dashboard-for-wp"),
			__("noted", "google-analytics-dashboard-for-wp"),
			__("selected", "google-analytics-dashboard-for-wp"),
			__("shrewd", "google-analytics-dashboard-for-wp"),
			__("growth", "google-analytics-dashboard-for-wp"),
			__("luxury", "google-analytics-dashboard-for-wp"),
			__("sturdy", "google-analytics-dashboard-for-wp"),
			__("enormous", "google-analytics-dashboard-for-wp"),
			__("promising", "google-analytics-dashboard-for-wp"),
			__("unconditional", "google-analytics-dashboard-for-wp"),
			__("wealth", "google-analytics-dashboard-for-wp"),
			__("spotlight", "google-analytics-dashboard-for-wp"),
			__("astonishing", "google-analytics-dashboard-for-wp"),
			__("timely", "google-analytics-dashboard-for-wp"),
			__("successful", "google-analytics-dashboard-for-wp"),
			__("useful", "google-analytics-dashboard-for-wp"),
			__("imagination", "google-analytics-dashboard-for-wp"),
			__("bonanza", "google-analytics-dashboard-for-wp"),
			__("opportunities", "google-analytics-dashboard-for-wp"),
			__("survival", "google-analytics-dashboard-for-wp"),
			__("greatest", "google-analytics-dashboard-for-wp"),
			__("security", "google-analytics-dashboard-for-wp"),
			__("last minute", "google-analytics-dashboard-for-wp"),
			__("largest", "google-analytics-dashboard-for-wp"),
			__("high tech", "google-analytics-dashboard-for-wp"),
			__("refundable", "google-analytics-dashboard-for-wp"),
			__("monumental", "google-analytics-dashboard-for-wp"),
			__("colossal", "google-analytics-dashboard-for-wp"),
			__("latest", "google-analytics-dashboard-for-wp"),
			__("quickly", "google-analytics-dashboard-for-wp"),
			__("startling", "google-analytics-dashboard-for-wp"),
			__("now", "google-analytics-dashboard-for-wp"),
			__("important", "google-analytics-dashboard-for-wp"),
			__("revolutionary", "google-analytics-dashboard-for-wp"),
			__("quick", "google-analytics-dashboard-for-wp"),
			__("unlock", "google-analytics-dashboard-for-wp"),
			__("urgent", "google-analytics-dashboard-for-wp"),
			__("miracle", "google-analytics-dashboard-for-wp"),
			__("easy", "google-analytics-dashboard-for-wp"),
			__("fortune", "google-analytics-dashboard-for-wp"),
			__("amazing", "google-analytics-dashboard-for-wp"),
			__("magic", "google-analytics-dashboard-for-wp"),
			__("direct", "google-analytics-dashboard-for-wp"),
			__("authentic", "google-analytics-dashboard-for-wp"),
			__("exciting", "google-analytics-dashboard-for-wp"),
			__("proven", "google-analytics-dashboard-for-wp"),
			__("simple", "google-analytics-dashboard-for-wp"),
			__("announcing", "google-analytics-dashboard-for-wp"),
			__("portfolio", "google-analytics-dashboard-for-wp"),
			__("reward", "google-analytics-dashboard-for-wp"),
			__("strange", "google-analytics-dashboard-for-wp"),
			__("huge gift", "google-analytics-dashboard-for-wp"),
			__("revealing", "google-analytics-dashboard-for-wp"),
			__("weird", "google-analytics-dashboard-for-wp"),
			__("value", "google-analytics-dashboard-for-wp"),
			__("introducing", "google-analytics-dashboard-for-wp"),
			__("sensational", "google-analytics-dashboard-for-wp"),
			__("surprise", "google-analytics-dashboard-for-wp"),
			__("insider", "google-analytics-dashboard-for-wp"),
			__("practical", "google-analytics-dashboard-for-wp"),
			__("excellent", "google-analytics-dashboard-for-wp"),
			__("delighted", "google-analytics-dashboard-for-wp"),
			__("download", "google-analytics-dashboard-for-wp"),
		);
	}

	/**
	 * Common words
	 *
	 * @return array common words
	 */
	function common_words() {
		return array(
			__("a", "google-analytics-dashboard-for-wp"),
			__("for", "google-analytics-dashboard-for-wp"),
			__("about", "google-analytics-dashboard-for-wp"),
			__("from", "google-analytics-dashboard-for-wp"),
			__("after", "google-analytics-dashboard-for-wp"),
			__("get", "google-analytics-dashboard-for-wp"),
			__("all", "google-analytics-dashboard-for-wp"),
			__("has", "google-analytics-dashboard-for-wp"),
			__("an", "google-analytics-dashboard-for-wp"),
			__("have", "google-analytics-dashboard-for-wp"),
			__("and", "google-analytics-dashboard-for-wp"),
			__("he", "google-analytics-dashboard-for-wp"),
			__("are", "google-analytics-dashboard-for-wp"),
			__("her", "google-analytics-dashboard-for-wp"),
			__("as", "google-analytics-dashboard-for-wp"),
			__("his", "google-analytics-dashboard-for-wp"),
			__("at", "google-analytics-dashboard-for-wp"),
			__("how", "google-analytics-dashboard-for-wp"),
			__("be", "google-analytics-dashboard-for-wp"),
			__("I", "google-analytics-dashboard-for-wp"),
			__("but", "google-analytics-dashboard-for-wp"),
			__("if", "google-analytics-dashboard-for-wp"),
			__("by", "google-analytics-dashboard-for-wp"),
			__("in", "google-analytics-dashboard-for-wp"),
			__("can", "google-analytics-dashboard-for-wp"),
			__("is", "google-analytics-dashboard-for-wp"),
			__("did", "google-analytics-dashboard-for-wp"),
			__("it", "google-analytics-dashboard-for-wp"),
			__("do", "google-analytics-dashboard-for-wp"),
			__("just", "google-analytics-dashboard-for-wp"),
			__("ever", "google-analytics-dashboard-for-wp"),
			__("like", "google-analytics-dashboard-for-wp"),
			__("ll", "google-analytics-dashboard-for-wp"),
			__("these", "google-analytics-dashboard-for-wp"),
			__("me", "google-analytics-dashboard-for-wp"),
			__("they", "google-analytics-dashboard-for-wp"),
			__("most", "google-analytics-dashboard-for-wp"),
			__("things", "google-analytics-dashboard-for-wp"),
			__("my", "google-analytics-dashboard-for-wp"),
			__("this", "google-analytics-dashboard-for-wp"),
			__("no", "google-analytics-dashboard-for-wp"),
			__("to", "google-analytics-dashboard-for-wp"),
			__("not", "google-analytics-dashboard-for-wp"),
			__("up", "google-analytics-dashboard-for-wp"),
			__("of", "google-analytics-dashboard-for-wp"),
			__("was", "google-analytics-dashboard-for-wp"),
			__("on", "google-analytics-dashboard-for-wp"),
			__("what", "google-analytics-dashboard-for-wp"),
			__("re", "google-analytics-dashboard-for-wp"),
			__("when", "google-analytics-dashboard-for-wp"),
			__("she", "google-analytics-dashboard-for-wp"),
			__("who", "google-analytics-dashboard-for-wp"),
			__("sould", "google-analytics-dashboard-for-wp"),
			__("why", "google-analytics-dashboard-for-wp"),
			__("so", "google-analytics-dashboard-for-wp"),
			__("will", "google-analytics-dashboard-for-wp"),
			__("that", "google-analytics-dashboard-for-wp"),
			__("with", "google-analytics-dashboard-for-wp"),
			__("the", "google-analytics-dashboard-for-wp"),
			__("you", "google-analytics-dashboard-for-wp"),
			__("their", "google-analytics-dashboard-for-wp"),
			__("your", "google-analytics-dashboard-for-wp"),
			__("there", "google-analytics-dashboard-for-wp"),
		);
	}


	/**
	 * Uncommon words
	 *
	 * @return array uncommon words
	 */
	function uncommon_words() {
		return array(
			__("actually", "google-analytics-dashboard-for-wp"),
			__("happened", "google-analytics-dashboard-for-wp"),
			__("need", "google-analytics-dashboard-for-wp"),
			__("thing", "google-analytics-dashboard-for-wp"),
			__("awesome", "google-analytics-dashboard-for-wp"),
			__("heart", "google-analytics-dashboard-for-wp"),
			__("never", "google-analytics-dashboard-for-wp"),
			__("think", "google-analytics-dashboard-for-wp"),
			__("baby", "google-analytics-dashboard-for-wp"),
			__("here", "google-analytics-dashboard-for-wp"),
			__("new", "google-analytics-dashboard-for-wp"),
			__("time", "google-analytics-dashboard-for-wp"),
			__("beautiful", "google-analytics-dashboard-for-wp"),
			__("its", "google-analytics-dashboard-for-wp"),
			__("now", "google-analytics-dashboard-for-wp"),
			__("valentines", "google-analytics-dashboard-for-wp"),
			__("being", "google-analytics-dashboard-for-wp"),
			__("know", "google-analytics-dashboard-for-wp"),
			__("old", "google-analytics-dashboard-for-wp"),
			__("video", "google-analytics-dashboard-for-wp"),
			__("best", "google-analytics-dashboard-for-wp"),
			__("life", "google-analytics-dashboard-for-wp"),
			__("one", "google-analytics-dashboard-for-wp"),
			__("want", "google-analytics-dashboard-for-wp"),
			__("better", "google-analytics-dashboard-for-wp"),
			__("little", "google-analytics-dashboard-for-wp"),
			__("out", "google-analytics-dashboard-for-wp"),
			__("watch", "google-analytics-dashboard-for-wp"),
			__("boy", "google-analytics-dashboard-for-wp"),
			__("look", "google-analytics-dashboard-for-wp"),
			__("people", "google-analytics-dashboard-for-wp"),
			__("way", "google-analytics-dashboard-for-wp"),
			__("dog", "google-analytics-dashboard-for-wp"),
			__("love", "google-analytics-dashboard-for-wp"),
			__("photos", "google-analytics-dashboard-for-wp"),
			__("ways", "google-analytics-dashboard-for-wp"),
			__("down", "google-analytics-dashboard-for-wp"),
			__("made", "google-analytics-dashboard-for-wp"),
			__("really", "google-analytics-dashboard-for-wp"),
			__("world", "google-analytics-dashboard-for-wp"),
			__("facebook", "google-analytics-dashboard-for-wp"),
			__("make", "google-analytics-dashboard-for-wp"),
			__("reasons", "google-analytics-dashboard-for-wp"),
			__("year", "google-analytics-dashboard-for-wp"),
			__("ﬁrst", "google-analytics-dashboard-for-wp"),
			__("makes", "google-analytics-dashboard-for-wp"),
			__("right", "google-analytics-dashboard-for-wp"),
			__("years", "google-analytics-dashboard-for-wp"),
			__("found", "google-analytics-dashboard-for-wp"),
			__("man", "google-analytics-dashboard-for-wp"),
			__("see", "google-analytics-dashboard-for-wp"),
			__("you’ll", "google-analytics-dashboard-for-wp"),
			__("girl", "google-analytics-dashboard-for-wp"),
			__("media", "google-analytics-dashboard-for-wp"),
			__("seen", "google-analytics-dashboard-for-wp"),
			__("good", "google-analytics-dashboard-for-wp"),
			__("mind", "google-analytics-dashboard-for-wp"),
			__("social", "google-analytics-dashboard-for-wp"),
			__("guy", "google-analytics-dashboard-for-wp"),
			__("more", "google-analytics-dashboard-for-wp"),
			__("something", "google-analytics-dashboard-for-wp"),
		);
	}
}

new ExactMetricsHeadlineToolPlugin();
