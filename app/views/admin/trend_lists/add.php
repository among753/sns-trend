<h2>Add Trend List</h2>

<?php echo $this->form->create($model->name); ?>
<?php echo $this->form->input('name'); ?>
<?php echo $this->form->input('description'); ?>
<?php echo $this->form->input('created'); ?>
<?php echo $this->form->input('modified'); ?>
<?php echo $this->form->end('Add'); ?>