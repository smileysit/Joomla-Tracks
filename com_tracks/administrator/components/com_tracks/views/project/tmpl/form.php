<?php
/**
* @version    $Id: form.php 145 2008-06-24 17:28:05Z julv $ 
* @package    JoomlaTracks
* @copyright	Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla Tracks is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>
<?php JHTML::_('behavior.formvalidation'); ?>

<?php
	// Set toolbar items for the page
	$edit		= JRequest::getVar('edit',true);
	$text = !$edit ? JText::_('COM_TRACKS_New' ) : JText::_('COM_TRACKS_Edit' );
	JToolBarHelper::title(   JText::_('COM_TRACKS_Project' ).': <small><small>[ ' . $text.' ]</small></small>' );
	JToolBarHelper::save();
	JToolBarHelper::apply();
	if (!$edit)  {
		JToolBarHelper::cancel();
	} else {
		// for existing items the button is renamed `close`
		JToolBarHelper::cancel( 'cancel', 'Close' );
	}
    JToolBarHelper::help( 'screen.tracks.edit', true );
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		// do field validation
		var validator = document.formvalidator;
		if ( validator.validate(form.name) === false ){
			alert( "<?php echo JText::_('COM_TRACKS_PROJECT_NAME_IS_REQUIRED', true ); ?>" );
    } else if ( validator.validate(form.competition_id) === false ){
      alert( "<?php echo JText::_('COM_TRACKS_COMPETITION_IS_REQUIRED', true ); ?>" );
		} else if ( validator.validate(form.season_id) === false ){
      alert( "<?php echo JText::_('COM_TRACKS_SEASON_IS_REQUIRED', true ); ?>" );
    } else {
			submitform( pressbutton );
		}
	}
</script>

<div id="tracksmain">
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_TRACKS_General' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="name">
					<?php echo JText::_('COM_TRACKS_Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area required" type="text" name="name" id="name" size="32" maxlength="250" value="<?php echo $this->project->name?>" />
			</td>
		</tr>
	  <tr>
	    <td width="100" align="right" class="key"><label for="alias"> <?php echo JText::_('COM_TRACKS_Alias' ); ?>:
	    </label></td>
	    <td><input class="text_area" type="text" name="alias" id="alias"
	      size="32" maxlength="250" value="<?php echo $this->project->alias?>" />
	    </td>
	  </tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_('COM_TRACKS_Published' ); ?>:
			</td>
			<td>
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<label for="competition_id">
					<?php echo JText::_('COM_TRACKS_Competition' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['competitions']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<label for="season_id">
					<?php echo JText::_('COM_TRACKS_Season' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['seasons']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<label for="admin_id">
					<?php echo JText::_('COM_TRACKS_Administrator' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['admin']; ?>
			</td>
		</tr>
		<tr>
      <td valign="top" align="right" class="key">
        <label for="ordering">
          <?php echo JText::_('COM_TRACKS_Ordering' ); ?>:
        </label>
      </td>
      <td>
        <?php echo $this->lists['ordering']; ?>
      </td>
    </tr>
	</table>
	</fieldset>
	
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_TRACKS_Parameters' ); ?></legend>

		<table class="admintable">
		<tr>
			<td colspan="2">
			<?php
	    // Iterate through the normal form fieldsets and display each one.
	    foreach ($this->params->getFieldsets() as $fieldsets => $fieldset):
	    ?>
	    <fieldset class="adminform">
	        <legend>
	            <?php echo JText::_($fieldset->name.'_jform_fieldset_label'); ?>
	        </legend>
	        <dl>
			<?php
			// Iterate through the fields and display them.
			foreach($this->params->getFieldset($fieldset->name) as $field):
			    // If the field is hidden, only use the input.
			    if ($field->hidden):
			        echo $field->input;
			    else:
			    ?>
			    <dt>
			        <?php echo $field->label; ?>
			    </dt>
			    <dd<?php echo ($field->type == 'Editor' || $field->type == 'Textarea') ? ' style="clear: both; margin: 0;"' : ''?>>
			        <?php echo $field->input ?>
			    </dd>
			    <?php
			    endif;
			endforeach;
			?>
			</dl>
	    </fieldset>
	    <?php
	    endforeach;
	    ?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>

<div class="clr"></div>

<input type="hidden" name="option" value="com_tracks" />
<input type="hidden" name="controller" value="project" />
<input type="hidden" name="cid[]" value="<?php echo $this->project->id; ?>" />
<input type="hidden" name="task" value="" />
</form>
</div>