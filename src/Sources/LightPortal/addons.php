<?php declare(strict_types=1);

use LightPortal\Lists\PluginList;
use LightPortal\Plugins\PluginHandler;

use function LightPortal\app;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'SSI.php';

$links = [
	'donate' => [
		'ApexCharts'  => 'https://ko-fi.com/post/ApexCharts-addon-for-Light-Portal-Z8Z4VKQ33',
		'DiceBear'    => 'https://ko-fi.com/post/DiceBear-addon-for-Light-Portal-N4N7VKQ79',
		'ImageUpload' => 'https://ko-fi.com/post/ImageUpload-addon-for-Light-Portal-X8X3VKQDT',
		'IndexNow'    => 'https://ko-fi.com/post/IndexNow-addon-for-Light-Portal-R5R4VKQCB',
		'Jodit'       => 'https://ko-fi.com/post/Jodit-addon-for-Light-Portal-Y8Y1VKQAM',
		'MediaBlock'  => 'https://ko-fi.com/post/MediaBlock-addon-for-Light-Portal-B0B5VKQ8P',
		'PageScroll'  => 'https://ko-fi.com/post/PageScroll-addon-for-Light-Portal-C0C7VKQGE',
	],
	'download' => [
		'AdsBlock'            => 'https://drive.proton.me/urls/4093NM54BM#e2MvDlJTZIEl',
		'ArticleList'         => 'https://drive.proton.me/urls/SWBK81MDJC#hhJozrFnOigI',
		'BlogMode'            => 'https://drive.proton.me/urls/PQNPYB0QTM#QP2Ay6ZzN8B8',
		'BoardList'           => 'https://drive.proton.me/urls/ND6CYM1KDG#oZgolyhqawrC',
		'BoardNews'           => 'https://drive.proton.me/urls/DH78SEZS60#r3YcWcwN5xJu',
		'BoardStats'          => 'https://drive.proton.me/urls/F2EE97CYWM#TmGFCE8VDQF8',
		'BootstrapIcons'      => 'https://drive.proton.me/urls/W1PKAW1P5C#EmW7fKAUivX7',
		'Calculator'          => 'https://drive.proton.me/urls/CJ295PSH1R#FeZuKEoQYKpT',
		'CategoryList'        => 'https://drive.proton.me/urls/E3NCB4T9JW#mZOMhuyDS0MI',
		'Chart'               => 'https://drive.proton.me/urls/DGQ2KX5M64#d3RkcKYYK02Q',
		'ChessBoard'          => 'https://drive.proton.me/urls/AM8C1MHRN8#Huduk8XSixl0',
		'CodeMirror'          => 'https://drive.proton.me/urls/361V1G0TSG#8h9dEjzmGYih',
		'CurrentMonth'        => 'https://drive.proton.me/urls/JTTEBXPJCG#USTO93SSkWGw',
		'Disqus'              => 'https://drive.proton.me/urls/FYJWEHDBPM#SALWktzQstxx',
		'Dragula'             => 'https://drive.proton.me/urls/N7Y9VNSWP0#3Zn4cPhOBAF1',
		'DummyArticleCards'   => 'https://drive.proton.me/urls/HNRCHB71CR#r8H4C74Gr3xb',
		'EasyMarkdownEditor'  => 'https://drive.proton.me/urls/R8JQXX09G8#6Th0unfbyFyK',
		'EhPortalMigration'   => 'https://drive.proton.me/urls/JB0WFCEFSM#s7PJPLGnljUM',
		'Events'              => 'https://drive.proton.me/urls/6ZHWRV13YC#KntTdiIAECjY',
		'ExtendedMetaTags'    => 'https://drive.proton.me/urls/D80YTRWT9M#D6QoIyWv178G',
		'EzPortalMigration'   => 'https://drive.proton.me/urls/RCN0DZ2T2W#Hg1jClvi3gUD',
		'FaBoardIcons'        => 'https://drive.proton.me/urls/N12FM9R5GG#b5JnAQYnd5tE',
		'FacebookComments'    => 'https://drive.proton.me/urls/A5PPYVT7MC#ossowqK7l9pD',
		'GalleryBlock'        => 'https://drive.proton.me/urls/BP1GDQ292M#3I1lq4mxK21v',
		'Giscus'              => 'https://drive.proton.me/urls/R7BJKSJNH4#xdrmpoKhZvlR',
		'HelloPortal'         => 'https://drive.proton.me/urls/40V4PK52CW#mwjkU1KWIeum',
		'HidingBlocks'        => 'https://drive.proton.me/urls/F3RFSZ6T54#7MBVSzLzj9z5',
		'LanguageAccess'      => 'https://drive.proton.me/urls/55R93ESWRM#zzlRSLdWCCUP',
		'LatteLayouts'        => 'https://drive.proton.me/urls/MWSG89JF7R#gv4bTs8GNp8t',
		'Likely'              => 'https://drive.proton.me/urls/XMNNHN0FJ4#zgCqwIikcI2Q',
		'LineAwesomeIcons'    => 'https://drive.proton.me/urls/ZHPMB64RNR#NOUoOkJ9BWfb',
		'MainIcons'           => 'https://drive.proton.me/urls/ZXQA4T3QE0#Rob2CLXsuX1N',
		'MainMenu'            => 'https://drive.proton.me/urls/2WV4KQ168R#L4QoIL7C9XCY',
		'Markdown'            => 'https://drive.proton.me/urls/MZB9Y5TK2G#JPfzRElZIidp',
		'MaterialDesignIcons' => 'https://drive.proton.me/urls/A7CPBPNXC8#tW7pmvKDJReF',
		'Memory'              => 'https://drive.proton.me/urls/Z9FFMXTMMC#fDQpk8KT0Xyk',
		'News'                => 'https://drive.proton.me/urls/T3BZ7B7XC4#e2QpF9sDa0WM',
		'Optimus'             => 'https://drive.proton.me/urls/R6YPCK2V3W#yVpWPHEDH4JV',
		'PageList'            => 'https://drive.proton.me/urls/D5FFEE8QE4#OgKQMeN8iVR1',
		'PluginMaker'         => 'https://drive.proton.me/urls/GW711ZDF14#9gkH7zIUbeOD',
		'Polls'               => 'https://drive.proton.me/urls/VF4A68J0E8#amS92BDh19V1',
		'PrettyUrls'          => 'https://drive.proton.me/urls/1RZFHTC3K0#21UpWAppETyQ',
		'RandomPages'         => 'https://drive.proton.me/urls/D1379A30CM#z79uEy1wbedy',
		'RandomTopics'        => 'https://drive.proton.me/urls/7NREY52E28#ViubQOcpaPFj',
		'Reactions'           => 'https://drive.proton.me/urls/BD7WE5TFZ0#yV1jxCj9F3cI',
		'RecentAttachments'   => 'https://drive.proton.me/urls/EGASMC37Q0#DWfumwswVXIx',
		'RecentComments'      => 'https://drive.proton.me/urls/YH5KEGXSKM#z07wkUoHuJqJ',
		'RecentPosts'         => 'https://drive.proton.me/urls/GHT3EJZ530#bZyYEsW2bPLN',
		'RecentTopics'        => 'https://drive.proton.me/urls/P1D4QV4RD8#8pvVb7K2YOWI',
		'Search'              => 'https://drive.proton.me/urls/07V9A21PM8#1pBTaH1aOtdW',
		'SimpleChat'          => 'https://drive.proton.me/urls/PC3SDWW8P8#6GTyBHHBvnJW',
		'SimpleFeeder'        => 'https://drive.proton.me/urls/VTVQ3J64EW#HeTyxNEW0iDt',
		'SimpleMenu'          => 'https://drive.proton.me/urls/R8WEAZN1SM#3PqkK3HyBnjg',
		'SiteList'            => 'https://drive.proton.me/urls/CWXGHB335C#wOrmOdADkVxh',
		'Snowflakes'          => 'https://drive.proton.me/urls/T1XN94DQWR#C25gEUa6r1Az',
		'Sudoku'              => 'https://drive.proton.me/urls/GW15VC1FY8#BRF3FruIADWb',
		'Swiper'              => 'https://drive.proton.me/urls/KH7XJW3CJW#jhqc79dTeGmz',
		'TagList'             => 'https://drive.proton.me/urls/6D1GHV3WZ8#9sknnLM5b8aN',
		'TelegramComments'    => 'https://drive.proton.me/urls/0TSC27FM4C#r5cwPvjXCJhV',
		'ThemeSwitcher'       => 'https://drive.proton.me/urls/PB1VM6TB3W#tk00qJR6fPnS',
		'TinyMCE'             => 'https://drive.proton.me/urls/VJJMMS0QW0#MDaRSKt1GLlR',
		'TinyPortalMigration' => 'https://drive.proton.me/urls/KM263NDDMW#zA0lRssASUts',
		'TinySlider'          => 'https://drive.proton.me/urls/3G0DH6P9X8#jH42SNLWRKfZ',
		'TopBoards'           => 'https://drive.proton.me/urls/RAVTFR4TV8#NVDwaMIQvyhC',
		'TopicRatingBar'      => 'https://drive.proton.me/urls/YH3SJWA6H0#74LygHrORhAD',
		'TopPages'            => 'https://drive.proton.me/urls/N2WP216CN8#ldSTwStDQthH',
		'TopPosters'          => 'https://drive.proton.me/urls/Y7S2177NR8#Wb2FnpwFi8ub',
		'TopTopics'           => 'https://drive.proton.me/urls/XJNAH3W5Y8#5Soj8BrewdH9',
		'Translator'          => 'https://drive.proton.me/urls/KFAYXFEC8G#EXuX9dgI8JMZ',
		'TrendingTopics'      => 'https://drive.proton.me/urls/RZ8ZJED7Y0#N6JM0mwVpisV',
		'TwentyFortyEight'    => 'https://drive.proton.me/urls/E75WV7V19C#mNpB0tCFAmgf',
		'TwigLayouts'         => 'https://drive.proton.me/urls/D09WRYV2SC#2Rk3AS4MLFcD',
		'Uicons'              => 'https://drive.proton.me/urls/722EMVYP90#6969OQwUX5Zk',
		'UserInfo'            => 'https://drive.proton.me/urls/1ZJH3ZPB38#hjdIO8B6fqUX',
		'VkComments'          => 'https://drive.proton.me/urls/8HBTBBBH14#NGJuyomoEjwj',
		'WhosOnline'          => 'https://drive.proton.me/urls/24J9FXECHR#nf9c5wNAYV1r',
	]
];

$list = $txtData = [];
$list['version'] = LP_VERSION;

$pluginList = app(PluginList::class)();
$handler = app(PluginHandler::class)($pluginList);
$plugins = $handler->getLoadedPlugins();

$i = 0;

foreach ($links as $type => $plugin) {
	foreach ($plugins as $data) {
		if (empty($plugin[$data['name']]))
			continue;

		$file = LP_ADDON_DIR . DIRECTORY_SEPARATOR . $data['name'] . DIRECTORY_SEPARATOR . $data['name'] . '.php';
		$docBlock = file_get_contents($file);

		$version = '';
		if ($docBlock && preg_match('/@version\s+([0-9]+\.[0-9]+\.[0-9]+)/', $docBlock, $matches)) {
			$version = $matches[1];
		}

		$list[$type][$i] = [
			'name'    => $data['name'],
			'link'    => $plugin[$data['name']],
			'type'    => $data['type'],
			'version' => $version,
		];

		$files = glob(LP_ADDON_DIR . DIRECTORY_SEPARATOR . $data['name'] . '/langs/*.php');

		foreach ($files as $filename) {
			$shortname = basename($filename, '.php');

			if ($shortname === 'index')
				continue;

			$txtData[$shortname] = require $filename;

			$list[$type][$i]['languages'][$shortname] = $txtData[$shortname]['description'] ?? '';
		}

		$origValues = $list[$type][$i]['languages'];

		$uniqueValues = array_filter($origValues, fn($value) => $value !== $origValues['english']);
		$uniqueValues['english'] = $origValues['english'];

		$list[$type][$i]['languages'] = array_intersect_key($origValues, $uniqueValues);

		$i++;
	}
}

file_put_contents(__DIR__ . '/addons.json', json_encode($list, JSON_UNESCAPED_UNICODE));

echo 'Done';
