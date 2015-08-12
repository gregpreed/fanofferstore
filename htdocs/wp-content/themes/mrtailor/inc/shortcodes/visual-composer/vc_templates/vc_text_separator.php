<?php

$output = $after_output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class = "vc_separator wpb_content_element";

$class .= ($title_align!='') ? ' vc_'.$title_align : '';
$class .= ($el_width!='') ? ' vc_el_width_'.$el_width : ' vc_el_width_100';
$class .= ($style!='') ? ' vc_sep_'.$style : '';
$class .= ($color!='') ? ' vc_sep_color_'.$color : '';

$inline_css = ($accent_color!='') ? ' style="'.vc_get_css_color('border-color', $accent_color).'"' : '';

$class .= $this->getExtraClass($el_class);
$css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base']);

?>
<div class="<?php echo esc_attr(trim($css_class)); ?>">
	<span class="vc_sep_holder vc_sep_holder_l"><span<?php echo $inline_css; ?> class="vc_sep_line"></span></span>
	<?php if($title!=''): ?>
    	<?php echo '<'.$title_size.'>'; ?><?php echo $title; ?><?php echo '</'.$title_size.'>'; ?>
	<?php endif; ?>
	<span class="vc_sep_holder vc_sep_holder_r"><span<?php echo $inline_css; ?> class="vc_sep_line"></span></span>
</div>
<?php if($subtitle!=''): ?>
    <div class="vc_sep_subtitle">
        <?php echo '<'.$subtitle_size.'>'; ?><?php echo $subtitle; ?><?php echo '</'.$subtitle_size.'>'; ?>
    </div>
<?php endif; ?>
<?php echo $this->endBlockComment('separator')."\n";