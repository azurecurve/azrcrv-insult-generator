<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name:		Insult Generator
 * Description:		Uses shortcodes which allow a modern or medieval insult to be generated..
 * Version:			1.2.6
 * Requires CP:		1.0
 * Author:			azurecurve
 * Author URI:		https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/insult-generator/
 * Donate link:		https://development.azurecurve.co.uk/support-development/
 * Text Domain:		insult-generator
 * Domain Path:		/languages
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.html
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname( __FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_ig');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */
// add actions
add_action('admin_menu', 'azrcrv_ig_create_admin_menu');
add_action('network_admin_menu', 'azrcrv_ig_create_network_admin_menu');
add_action('wp_enqueue_scripts', 'azrcrv_ig_load_css');
add_action('plugins_loaded', 'azrcrv_ig_load_languages');

// add filters
add_filter('plugin_action_links', 'azrcrv_ig_add_plugin_action_link', 10, 2);
add_filter('the_posts', 'azrcrv_ig_check_for_shortcode', 10, 2);
add_filter('codepotent_update_manager_image_path', 'azrcrv_ig_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_ig_custom_image_url');

// add shortcodes
add_shortcode('MODERNINSULT', 'azrcrv_ig_modern_insult');
add_shortcode('moderninsult', 'azrcrv_ig_modern_insult');
add_shortcode('MEDIEVALINSULT', 'azrcrv_ig_medieval_insult');
add_shortcode('medievalinsult', 'azrcrv_ig_medieval_insult');
add_shortcode('displayinsult', 'azrcrv_ig_display_insult');
add_shortcode('DISPLAYINSULT', 'azrcrv_ig_display_insult');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('insult-generator', false, $plugin_rel_path);
}

/**
 * Check if shortcode on current page and then load css and jqeury.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_check_for_shortcode($posts){
    if (empty($posts)){
        return $posts;
	}
	
	
	// array of shortcodes to search for
	$shortcodes = array(
						'MODERNINSULT','moderninsult','MEDIEVALINSULT','medievalinsult','DISPLAYINSULT','displayinsult'
						);
	
    // loop through posts
    $found = false;
    foreach ($posts as $post){
		// loop through shortcodes
		foreach ($shortcodes as $shortcode){
			// check the post content for the shortcode
			if (has_shortcode($post->post_content, $shortcode)){
				$found = true;
				// break loop as shortcode found in page content
				break 2;
			}
		}
	}
 
    if ($found){
		// as shortcode found call functions to load css and jquery
        azrcrv_ig_load_css();
    }
    return $posts;
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_load_css(){
	wp_enqueue_style('azrcrv-ig', plugins_url('assets/css/style.css', __FILE__));
}

/**
 * Custom plugin image path.
 *
 * @since 1.2.0
 *
 */
function azrcrv_ig_custom_image_path($path){
    if (strpos($path, 'azrcrv-insult-generator') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.2.0
 *
 */
function azrcrv_ig_custom_image_url($url){
    if (strpos($url, 'azrcrv-insult-generator') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Add action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-ig').'"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'insult-generator').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_create_admin_menu(){
	//global $admin_page_hooks;
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("Insult Generator Settings", "insult-generator")
						,esc_html__("Insult Generator", "insult-generator")
						,'manage_options'
						,'azrcrv-ig'
						,'azrcrv_ig_display_options');
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'insult-generator'));
    }
	
	?>
	<div id="azrcrv-ig-general" class="wrap">
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
				esc_html_e(get_admin_page_title());
			?>
		</h1>
		<p>
			<?php esc_html_e('This plugin works by providing three shortcodes which can be positioned on a post, page or widget:
<ul><li>moderninsult when clicked will generate a modern insult displayed using the displayinsult shortcode</li>
<li>medievalinsult when clicked will generate a medieval insult displayed using the displayinsult shortcode</li>
<li>displayinsult is used to position the insult generated by one of the other shortcodes</li></ul>', 'insult-generator'); ?>
		</p>
		<p><label for="additional-plugins">
			azurecurve <?php esc_html_e('has the following plugins which allow shortcodes to be used in comments and widgets:', 'insult-generator'); ?>
		</label>
		<ul class='azrcrv_plugin_index'>
			<li>
				<?php
				if (azrcrv_i_is_plugin_active('azurecurve-shortcodes-in-comments/azurecurve-shortcodes-in-comments.php')){
					echo "<a href='admin.php?page=azrcrv-sic' class='azrcrv_plugin_index'>Shortcodes in Comments</a>";
				}else{
					echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/' class='azrcrv_plugin_index'>Shortcodes in Comments</a>";
				}
				?>
			</li>
			<li>
				<?php
				if (azrcrv_i_is_plugin_active('azurecurve-shortcodes-in-widgets/azurecurve-shortcodes-in-widgets.php')){
					echo "<a href='admin.php?page=azrcrv-siw' class='azrcrv_plugin_index'>Shortcodes in Widgets</a>";
				}else{
					echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-widgets/' class='azrcrv_plugin_index'>Shortcodes in Widgets</a>";
				}
				?>
			</li>
		</ul></p>
	</div>
	<?php
}

/**
 * Add to Network menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_create_network_admin_menu(){
	if (function_exists('is_multisite') && is_multisite()){
		add_submenu_page(
						'settings.php'
						,esc_html__("Insult Generator Settings", "insult-generator")
						,esc_html__("Insult Generator", "insult-generator")
						,'manage_network_options'
						,'azrcrv-ig'
						,'azrcrv_ig_network_settings'
						);
	}
}

/**
 * Display network settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_network_settings(){
	if(!current_user_can('manage_network_options')) wp_die(esc_html__('You do not have permissions to perform this action', 'azrcrv-ig'));

	?>
	<div id="azrcrv-ig-general" class="wrap">
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
				esc_html_e(get_admin_page_title());
			?>
		</h1>
		<p>
			<?php esc_html_e('This plugin works by providing three shortcodes which can be positioned on a post, page or widget:
<ul><li>moderninsult when clicked will generate a modern insult displayed using the displayinsult shortcode</li>
<li>medievalinsult when clicked will generate a medieval insult displayed using the displayinsult shortcode</li>
<li>displayinsult is used to position the insult generated by one of the other shortcodes</li></ul>', 'insult-generator'); ?>
		</p>
		<p><label for="additional-plugins">
			azurecurve <?php esc_html_e('has the following plugins which allow shortcodes to be used in comments and widgets:', 'insult-generator'); ?>
		</label>
		<ul class='azrcrv_plugin_index'>
			<li>
				<?php
				if (azrcrv_i_is_plugin_active('azurecurve-shortcodes-in-comments/azurecurve-shortcodes-in-comments.php')){
					echo "<a href='admin.php?page=azrcrv-sic' class='azrcrv_plugin_index'>Shortcodes in Comments</a>";
				}else{
					echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/' class='azrcrv_plugin_index'>Shortcodes in Comments</a>";
				}
				?>
			</li>
			<li>
				<?php
				if (azrcrv_i_is_plugin_active('azurecurve-shortcodes-in-widgets/azurecurve-shortcodes-in-widgets.php')){
					echo "<a href='admin.php?page=azrcrv-siw' class='azrcrv_plugin_index'>Shortcodes in Widgets</a>";
				}else{
					echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-widgets/' class='azrcrv_plugin_index'>Shortcodes in Widgets</a>";
				}
				?>
			</li>
		</ul></p>
	</div>
	<?php
}

/**
 * Check if function active (included due to standard function failing due to order of load).
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_is_plugin_active($plugin){
    return in_array($plugin, (array) get_option('active_plugins', array()));
}

/**
 * Modern Insult Shortcode displays button.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_modern_insult($atts, $content = null){
	global $wp;
	
	$current_url = home_url(add_query_arg(array(), $wp->request));
	
	return '<span class="azrcrv-ig-modern">
		<fieldset>
			<form method="post" action="'. $current_url .'">
				<input type="submit" name="modinsult" value="Generate Modern Insult" class="azrcrv-ig-modern"/>
			</form>
		</fieldset>
	</span>';
}

/**
 * Medieval Insult Shortcode displays button.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_medieval_insult($atts, $content = null){
	global $wp;
	
	$current_url = home_url(add_query_arg(array(), $wp->request));
	
	return '<span class="azrcrv-ig-medieval">
		<fieldset>
			<form method="post" action="'. $current_url .'">
				<input type="submit" name="medinsult" value="Generate Medieval Insult" class="azrcrv-ig-medieval"/>
			</form>
		</fieldset>
	</span>';
}

/**
 * Display Insult Shortcode outputs modern or medieval insult depending on which shortcode button pressed.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ig_display_insult($atts, $content = null){
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		if ($_POST["modinsult"]){
			$modinsultone = array("amorphous","annoying","appalling","asanine","atrocious","bad breathed","bad tempered","bewildered","bizarre","blithering","blundering","boring","brainless","bungling","cantakerous","clueless","confused","contemptible","cranky","crooked","crotchety","crude","decrepit","deeply disturbed","demented","deparaved","deranged","despicable","detestable","dim-witted","disdainful","disgraceful","disgusting","dismal","distasteful","distended","double-ugly","dreadful","drivelling","dull","dumb","dumpy","embarrassing","eratic","evil-smelling","facile","feeble-minded","flea-bitten","fractious","frantic","friendless","glutinous","god-awful","good-for-nothing","goofy","grotesque","hallucinating","hopeless","hypocritical","ignorant","illiterate","inadequate","inane","incapable","incompetent","incredible","indecent","indescribable","inept","infantile","infuriating","inhuman","insane","insignificant","insurrerable","irrational","irresponsible","lacklustre","laughable","lazy","loathsome","low budget","malodorous","mentally deficient","misanthropic","miserable","monotonous","nauseating","neurotic","obese","oblivious","obnoxious","obsequious","offensive","opinionated","outrageous","pathetic","perverted","pitiable","pitiful","pointless","pompous","predictable","preposterous","psychotic","pustulant","rabbit-faced","rat-faced","recalcitrant","reprehensible","repulsive","retarded","revolting","ridiculous","rotund","self-exalting","shameless","sick","sickening","sleazy","sloppy","slovenly","spittle-encrusted","stupid","subhuman","superficial","sychophantic","tacky","tiny-brained","ugly","unbelievable","uncouth","uncultivated","uncultured","undisciplined","uneducated","ungodly","unholy","unimpressive","unspeakable","unwelcome","upsetting","useless","vile","violent","vomitous","witless","worthless");
			$modinsulttwo = array("accumulation of","apology for","assortment of","bag of","ball of","barrel of","blob of","bowl of","box of","bucket of","bunch of","cake of","clump of","collection of","container of","contribution of","crate of","crock of","eruption of","excuse for","glob of","heap of","load of","loaf of","lump of","mass of","mound of","mountain of","pile of","sack of","shovel-full of","stack of","toilet-full of","truckload of","tub of","vat of","wheelbarrow-full of");
			$modinsultthree = array("cheesy","clammy","contaminated","crummy","crusty","cute","decaying","decomposed","defective","dehydrated","dirty","dusky","embarrassing","fermenting","festering","filthy","flea-bitten","fly-covered","foreign","fornicating","fossilised","foul","freeze-dried","fresh","fusty","grimy","grisly","gross","gruesome","imitation","industrial-strength","infected","infested","laughable","lousy","malignant","mealy","mildewed","moth-eaten","mouldy","musty","mutilated","nasty","nauseating","noxious","old","petrified","polluted","putrid","radioactive","rancid","raunchy","recycled","reeky","rotten","second-hand","seething","septic","sloppy","sloshy","smelly","soggy","soppy","spoiled","stale","steaming","stenchy","sticky","stinky","sun-ripened","synthetic","unpleasant","wormy","yeasty");
			$modinsultfour = array("aardvark effluent","ape pucke","armpit hairs","baboon arses","bat guano","bile","braised pus","buffalo excrement","bug parts","buzzard barf","buzzard leavings","camel fleas","camel manure","carp guts","carrion","chicken guts","cigar butts","cockroaches","compost","cow cud","cow pies","coyote snot","dandruff flakes","dingo's kidneys","dirty underwear","dog barf","dog meat","dog phlegm","donkey droppings","drain clogs","earwax","eel guts","electric donkeys","elephant plaque","Ewok excrement","expectorant","fish lips","foot fungus","frog fat","garbage","goat carcasses","gutter mud","haemorroids","hippo vomit","hog livers","hog swill","hogswash","jockstraps","kangaroo vomit","knob cheese","larks vomit","leprosy scabs","lizard bums","llama spit","maggot brains","maggot fodder","maggot guts","monkey zips","moose entrails","mule froth","nasal hairs","navel lint","nose hairs","nose pickings","parrot droppings","penguino guano","penile warts","pig bowels","pig droppings","pig slop","pigeon bombs","pimple pus","pimple squeezings","puke lumps","pustulence","rabbit raisins","rat bogies","rat cysts","rodent droppings","rubbish","sewerage","sewer seepage","shark snot","sheep droppings","sinus clots","sinus drainage","skid marks","skunk waste","slime-mould","sludge","slug slime","snake innards","spittoon spillage","stable sweepings","stomach acid","swamp mud","sweat-socks","swine remains","toad tumors","tripe","turkey puke","vulture gizzards","walrus blubber","weasel warts","whale waste","zit cheese");
			$return = '<span class="azrcrv-ig-display">You are ';
			$modinsult = $modinsultone[mt_rand(0, count($modinsultone) - 1)];
			if (ctype_alpha($modinsult) && preg_match('/^[aeiou]/i', $modinsult)){
				$return .= 'an ';
			}else{
				$return .= 'a ';
			}
			$return .= $modinsult.' ';
			$return .= $modinsulttwo[mt_rand(0, count($modinsulttwo) - 1)].' ';
			$return .= $modinsultthree[mt_rand(0, count($modinsultthree) - 1)].' ';
			$return .= $modinsultfour[mt_rand(0, count($modinsultfour) - 1)];
			$return .= '.</span>';
		}elseif ($_POST["medinsult"]){
			$medinsultone = array("artless","bawdy","beslubbering","bootless","brazen","churlish","cloutered","cockered","craven","currish","dankish","dissembling","distempered","droning","errant","fawning","fitting","frobbing","frothing","froward","gleeking","gnarling","goatish","gorbellied","greasy","grizzled","haughty","hideous","impertinent","infectious","jaded","jarring","knavish","lewd","loggerhead","lumpish","mammering","magled","mewling","paunchy","peevish","pernicious","prating","pribling","puking","puny","purpled","qualling","queasey","rank","reeking","rougish","roynish","ruttish","saucy","sottish","spleeny","spongy","surly","tottering","unmuzzled","vacant","vain","venomed","villanous","waggish","wanton","warped","wayward","weeding","wenching","whoreson","yeasty");
			$medinsulttwo = array("base-court","bat-fowling","beef-witted","beetle-headed","boil-brained","bunched-backed","clapper-clawed","clay-brained","common-kissing","crook-pated","dismal-dreaming","dizzy-eyed","dog-hearted","dread-bolted","earth-vexing","elf-skinned","empty-hearted","evil-eyed","fat-kidneyed","fen-sucked","flap-mouthed","fly-bitten","folly-fallen","fool-born","full-gorged","guts-griping","half-faced","half-witted","hasty-witted","heavy-handed","hedge-born","hell-hated","horn-mad","idle-headed","ill-breeding","ill-nurtured","knotty-pated","lean-witted","mad-bread","milk-livered","motley-minded","muddy-mettled","onion-eyed","pale-hearted","paper-faced","pinch-spotted","plume-plucked","pottle-deep","pox-marked","raw-boned","reeling-ripe","rough-hewn","rude-growing","rug-headed","rump-fed","shag-eared","shard-norn","sheep-biting","shril-gorged","sour-faced","spur-galled","swag-bellied","tardy-gaited","tickle-brained","toad-spotted","unchin-snouted","weak-hinged","weather-bitten","white-livered");
			$medinsultthree = array("apple-john","baggage","barnacle","bladder","boar-pig","bugbear","bum-bailey","canker-blossom","clack-dish","clotpole","codpiece","coxcomb","crutch","cutpurse","death-token","dewberry","dogfish","eggshell","flap-dragon","flax-wench","flirt-gill","foot-licker","fustilarian","giglet","gudgeon","gull-catcher","haggard","harpy","hedge-pig","hempseed","horn-beast","hugger-mugger","jack-a-nape","jolthead","lewdster","lout","maggot-pie","malignancy","malkin","malt-worm","mammet","manikin","measle","minimus","minnow","miscreant","moldwarp","mumble-news","nut-hook","pantaloon","pigeon-egg","pignut","pumpion","puttock","rabbit-sucker","rampallion","ratsbane","remnant","rudesby","ruffian","scantling","scullion","scut","skainsmate","snipe","strumpet","varlot","vassal","wagtail","waterfly","whey-face","whipster","younker");
			$return = '<span class="azrcrv_ig">Thou art ';
			$medinsult = $medinsultone[mt_rand(0, count($medinsultone) - 1)];
			if (ctype_alpha($medinsult) && preg_match('/^[aeiou]/i', $medinsult)){
				$return .= 'an ';
			}else{
				$return .= 'a ';
			}
			$return .= $medinsult.' ';
			$return .= $medinsulttwo[mt_rand(0, count($medinsulttwo) - 1)].' ';
			$return .= $medinsultthree[mt_rand(0, count($medinsultthree) - 1)];
			$return .= '.</span>';
		}
		return $return;
	}
}

?>