<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.helper');
/**
 * Sermonspeaker Component HTML Helper
 */
class JHtmlIcon
{
	static function edit($item, $params, $attribs = array())
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$uri	= JFactory::getURI();

		// Ignore if Frontend Uploading is disabled
		if ($params && !$params->get('fu_enable')) {
			return;
		}

		// Ignore if in a popup window.
		if ($params && $params->get('popup')) {
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($item->state < 0) {
			return;
		}

		JHtml::_('behavior.tooltip');

		// Show checked_out icon if the item is checked out by a different user
		if (property_exists($item, 'checked_out') && property_exists($item, 'checked_out_time') && $item->checked_out > 0 && $item->checked_out != $user->get('id')) {
			$checkoutUser = JFactory::getUser($item->checked_out);
			$button = JHtml::_('image', 'system/checked_out.png', NULL, NULL, true);
			$date = JHtml::_('date', $item->checked_out_time);
			$tooltip = JText::_('JLIB_HTML_CHECKED_OUT').' :: '.JText::sprintf('COM_SERMONSPEAKER_CHECKED_OUT_BY', $checkoutUser->name).' <br /> '.$date;
			return '<span class="hasTip" title="'.htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8').'">'.$button.'</span>';
		}

		switch ($attribs['type']){
			default:
			case 'sermon':
				$view	= 'frontendupload';
				break;
			case 'serie':
				$view	= 'serieform';
				break;
			case 'speaker':
				$view	= 'speakerform';
				break;
		}

		$url	= 'index.php?option=com_sermonspeaker&task='.$view.'.edit&s_id='.$item->id.'&return='.base64_encode($uri);
		$icon	= $item->state ? 'edit.png' : 'edit_unpublished.png';
		$text	= JHtml::_('image', 'system/'.$icon, JText::_('JGLOBAL_EDIT'), NULL, true);

		if ($item->state == 0) {
			$overlib = JText::_('JUNPUBLISHED');
		}
		else {
			$overlib = JText::_('JPUBLISHED');
		}

		if($item->created != '0000-00-00 00:00:00'){
			$date = JHtml::_('date', $item->created);
			$overlib .= '&lt;br /&gt;';
			$overlib .= JText::sprintf('JGLOBAL_CREATED_DATE_ON', $date);
		}
		if($item->author){
			$overlib .= '&lt;br /&gt;';
			$overlib .= JText::_('JAUTHOR').': '.htmlspecialchars($item->author, ENT_COMPAT, 'UTF-8');
		}

		$button = JHtml::_('link', JRoute::_($url), $text);

		$output = '<span class="hasTip" title="'.JText::_('JACTION_EDIT').' :: '.$overlib.'">'.$button.'</span>';

		return $output;
	}
}
