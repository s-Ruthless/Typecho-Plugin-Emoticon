<?php

namespace TypechoPlugin\Emoticon;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Textarea;
use Typecho\Widget\Helper\Form\Element\Checkbox;
use Typecho\Widget\Helper\Form\Element\Radio;
use Typecho\Widget\Helper\Layout;
use Widget\Options;
use Widget\Base\Comments;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}


/**
 * Typecho博客支持表情插件
 * 
 * @package Emoticon
 * @author 梦繁星
 * @version 1.0.0
 * @link
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate() {
        \Typecho\Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'header');
        \Typecho\Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
        
        \Typecho\Plugin::factory('Widget_Abstract_Comments')->contentEx = array('Emoticon_Plugin','parseBiaoQing');
		\Typecho\Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Emoticon_Plugin','parseBiaoQing');
		\Typecho\Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Emoticon_Plugin','parseBiaoQing');
        
        \Typecho\Plugin::factory('admin/write-post.php')->bottom = array('Emoticon_Plugin','editbutton');
		\Typecho\Plugin::factory('admin/write-page.php')->bottom = array('Emoticon_Plugin','editbutton');
    	return _t('插件安装成功！');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate() {
    	return _t('插件禁用成功!');
    }

    /**
     * 获取插件配置面板
     * @param Form $form 配置面板
     */
    public static function config(Form $form) {
    	//表情大小限制
    	$emosize = new Text('emosize', null, '40', _t('表情大小限制'),_t('设置表情显示的最大宽度, 单位: px(不用填写)。Ctrl+F5清缓存看效果！'));
    	$emosize->input->setAttribute('class','text-s');
		$emosize->input->setAttribute('style','width:100px;');
		$form->addInput($emosize->addRule('isFloat'));
		
		//表情选择框大小限制
    	$emobarsize = new Text('emobarsize', null, '360', _t('表情选择框大小限制'),_t('设置表情选择框显示的最大宽度, 单位: px(不用填写)。Ctrl+F5清缓存看效果！'));
    	$emobarsize->input->setAttribute('class','text-s');
		$emobarsize->input->setAttribute('style','width:100px;');
		$form->addInput($emobarsize->addRule('isFloat'));
		
		//前台表情选择按钮样式
    	$emobarsty = new Textarea('emobarsty', null, 'width:60px;
height:25px;
color:#fff;
border-radius:2px;
font-size:12px;
text-align:center;
line-height:26px;
background-image:-webkit-linear-gradient(0deg,#3ca5f6 0%,#a86af9 100%);',
            _t('自定义前台表情选择按钮样式'),_t('自定义前台表情选择按钮样式，直接填写style样式。Ctrl+F5清缓存看效果！'));
    	$emobarsty->input->setAttribute('style','width:450px;');
		$form->addInput($emobarsty);
		
		//正文使用表情
        $postmode = new Radio('postmode',
		array(1=>_t('开启'),0=>_t('关闭')),1,_t('正文使用表情'),_t('编辑文章或页面时也可选择插入表情图片并发布显示'));
		$form->addInput($postmode);
		
		//引入jQ
        $radiojQ = new Radio('radiojQ',
		array(1=>_t('开启'),0=>_t('关闭')),0,_t('是否引入jQ'),_t('默认关闭，如果主题没有引入jQ导致功能失效，请开启此功能！</br></br>请在需要显示表情弹框按钮的位置插入下方嵌入点代码:</br><b><code style="padding: 2px 4px; font-size: 15px; color: #c7254e; background-color: #f9f2f4; border-radius: 4px;">&lt;?php Emoticon_Plugin::emoOut(); ?&gt;</code></b></br>可添加到&nbsp;<b>comments.php</b>&nbsp;文件提交评论旁。'));
		$form->addInput($radiojQ);
    	
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form) {
    }
    
    
    /*
    *嵌入点
    */
    public static function emoOut(){
        $options = Options::alloc();
        $setting = $options->plugin('Emoticon');
        $emobarsize=$setting->emobarsize;
        $emobarsty=$setting->emobarsty;
        $text='<div class="OwO-logo" style="'.$emobarsty.'"><span class="smile-icons"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg></span><span class="OwOlogotext">&nbsp;表情</span></div>';
        $emobody='<div class="bq-list" style="width:'.$emobarsize.'px">'.self::getBiaoQing().'</div>';
        echo $text.$emobody;
    }
    
    
    
    /*
    * 获取表情
    */
	public static function getBiaoQing() {
    	$owopath = false;
    	global $owopath;
    	if(!$owopath) {
    		$owopath = json_decode(file_get_contents(dirname(dirname(dirname(__FILE__))).'/plugins/Emoticon/emo/OwO.json'), true);
    	}
    	for ($i = 0; $i < count($owopath); $i++) {
    		$bar=array_keys($owopath);
    		$type=$owopath[$bar[$i]]['type'];
            $num=count($owopath[$bar[$i]]['container']);
    		$emo=$owopath[$bar[$i]]['container'];
    		
    		$emoname=$owopath[$bar[$i]]['name'];
    		
    		$ename=$owopath[$bar[$i]];
    		if ($type==='image') {
    			$ul=$ul.'<ul class="OwO-'.$ename['name'].'">'.self::wholeemo($num,$type,$ename,$emo,$emoname).'</ul>';
    		} else {
    			$ul=$ul.'<ul class="OwO-'.$type.'">'.self::wholeemo($num,$type,$ename,$emo,$emoname).'</ul>';
    		}
    		$div=$div.'<div class="OwO-bar-item">'.$bar[$i].'</div>';
    		
    	}
    	$divemo='<div class="OwO-bar">'.$div.'</div>';
    	$ulemo='<div class="OwO-emoji">'.$ul.'</div>';
    	return $divemo.$ulemo;
    }
    
    /*
    * 获取具体表情
    */
   public static function wholeemo($num,$type,$ename,$emo,$emoname) {
       $urlpath=$_SERVER['REQUEST_SCHEME'].":". DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $_SERVER['HTTP_HOST'];
       
    	if ($type==='image') {
    		for ($j = 0; $j < $num; $j++) {
    		    $emotext="$$".$emoname.":".$emo[$j]['icon']."$$";
    			$li=$li.'<li class="OwO-item" data-title="'.$emotext.'" title="'.$emo[$j]['text'].'"><img src="'.$urlpath.'/usr/plugins/Emoticon/emo/'.$ename['name'].'/'.$emo[$j]['icon'].'.png"></li>';
    		}
    		return $li;
    	} else {
    		for ($j = 0; $j < $num; $j++) {
    			$li=$li.'<li class="OwO-item" data-title="'.$emo[$j]['icon'].'" title="'.$emo[$j]['text'].'">'.$emo[$j]['icon'].'</li>';
    		}
    		return $li;
    	}
    }
    
    /*
    * 解析表情
    */
    public static function parseBiaoQing($content) {
        $options = Options::alloc();
        $setting = $options->plugin('Emoticon');
        $emosize=$setting->emosize;
        $options->commentsHTMLTagAllowed .= '<img src="" alt="" style=""/>';
    	$urlpath=$_SERVER['REQUEST_SCHEME'].":". DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $_SERVER['HTTP_HOST'];
    	$parseemo = false;
    	global $parseemo;
    	if(!$parseemo) {
     		$parseemo = json_decode(file_get_contents(dirname(dirname(dirname(__FILE__))).'/plugins/Emoticon/emo/OwO.json'), true);
    	}
    	foreach ($parseemo as $emorry) {
    		if($emorry['type'] == 'image') {
    			foreach ($emorry['container'] as $emo) {
    				$emostring="$$".$emorry['name'].":".$emo['icon']."$$";
    				$content = str_replace($emostring, '  <img style="max-height:'.$emosize.'px;vertical-align:middle;" src="'.$urlpath.'/usr/plugins/Emoticon/emo/'.$emorry['name'].'/'.$emo['icon'] .'.png"  alt="'.$emo['text'] .'">  ', $content);
    			}
    		}
    	}
    	return $content;
    }
    
     /**
     *为header添加文件
     *@return void
     */
	public static function header()
	{
		$options = Options::alloc();
		$cssUrl=$options->pluginUrl;
		$radiojQ=$options->plugin('Emoticon')->radiojQ;
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '/Emoticon/assets/emo.css" />';
        if ($radiojQ==1) {
            echo '<script type="text/javascript" src="' . $cssUrl . '/Emoticon/assets/jquery-3.6.0.min.js" /></script>';
        }
	}
	
	 /**
     *为footer添加js文件
     *@return void
     */
	public static function footer()
	{
		
		$options = Options::alloc();
		$cssUrl=$options->pluginUrl;
        echo '<script type="text/javascript" src="' . $cssUrl . '/Emoticon/assets/emo.js" /></script>';
        
	}
    
     /**
	 * 编辑器按钮
	 * 
	 * @access public
	 * @return void
	 */
	public static function editbutton(){
        $options = Options::alloc();
        $setting = $options->plugin('Emoticon');
        $emobarsize=$setting->emobarsize;
		if ($setting->postmode) {
			$smilies = self::getBiaoQing();
?>
    <link href="<?php Options::alloc()->pluginUrl('Emoticon/assets/emo.css'); ?>" rel="stylesheet"/>
    <script type="text/javascript" src="<?php Options::alloc()->pluginUrl('Emoticon/assets/emo.js'); ?>" /></script>
<script type="text/javascript">
    $(function() {
        var wmd = $('#wmd-image-button');
        wmd.after('<li class="wmd-button" id="wmd-owo-button" style="padding-top:5px;" title="<?php _e("插入表情"); ?>"><div class="OwO-logo-edi"><span class="smile-icons"><svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg></span><span class="OwOlogotext">表情</span></div><div class="bq-list" style="width:<?php echo $emobarsize;?>px"><?php echo $smilies;?></div></li>');
        $('.bq-list .OwO-bar-item:nth-child(1)').addClass('active');
        $('.OwO-emoji ul:nth-child(1)').addClass('active-txt');
        /* $("#wmd-owo-button").each(function(index) {
                 $(this).click(function() {
                     if ($('.bq-list').attr('class')==='bq-list') {
                         alert(666);
                     } else {
                          alert(888);
                     }
                   $('.bq-list').addClass('active');
                    $(".bq-list .OwO-bar-item").each(function(index) {
                        $(this).click(function() {
                            $(".OwO-bar-item.active").removeClass("active");
                            $(this).addClass("active");
                            $(".OwO-emoji ul.active-txt").removeClass("active-txt");
                            $(".OwO-emoji ul").eq(index).addClass("active-txt");
                            
                        });
                    });
                 });
                
            });*/

        $('#wmd-owo-button .OwO-logo-edi').click(function() {
            if ($('.bq-list').attr('class') === 'bq-list') {
                $('.bq-list').addClass('active');
            } else {
                $('.bq-list.active').removeClass('active');
            }

            $(".bq-list .OwO-bar-item").each(function(index) {
                $(this).click(function() {
                    $(".OwO-bar-item.active").removeClass("active");
                    $(this).addClass("active");
                    $(".OwO-emoji ul.active-txt").removeClass("active-txt");
                    $(".OwO-emoji ul").eq(index).addClass("active-txt");

                });
            });

        });
        $(".bq-list ul li").each(function(index) {
            $(this).click(function() {
                var txt = $(".bq-list ul li").eq(index).attr("data-title");
                $("#text").insertAtCaret(txt);
                $('.bq-list.active').removeClass('active');
            });
        });
    });
</script>

<?php

		}
	}

}
?>