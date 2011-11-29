<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'attribute'); ?>
		<?php echo $form->dropDownList($model,'attribute',$attributeList); ?>
		<?php echo $form->error($model,'attribute'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'comparison'); ?>
		<?php echo $form->dropDownList($model,'comparison',$comparisonList); ?>
		<?php echo $form->error($model,'comparison'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'value'); ?>
		<?php echo $form->textField($model,'value',array('size'=>'30')); ?>
		<?php echo $form->error($model,'value'); ?>
	</div>
</div>