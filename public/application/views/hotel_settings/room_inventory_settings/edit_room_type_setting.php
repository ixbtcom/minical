	
    <?php
				if (isset($room_type)) :
					$count = 1;
					 ?>
	
						<div class="panel panel-default room-type-div" id="<?php echo $room_type['id']; ?>">
							<div class="panel-body form-horizontal">
								<div class="alert alert-success hidden updated-message" role="alert"><?php echo l('Updated', true); ?>!</div>
								

								<div class="form-group">
									<label for="description" class="col-sm-3 control-label">
										<?php echo l('room_type_name'); ?>
									</label>
									<div class="col-sm-3">
										<input name="room-type-name" class="form-control" type="text" value="<?php echo $room_type['name']; ?>"/>
									</div>
									<label for="description" class="col-sm-2 control-label">
										<?php echo l('acronym'); ?>
									</label>
									<div class="col-sm-2">
										<input name="room-type-acronym" class="form-control" type="text" size="6" maxlength="6" value="<?php echo $room_type['acronym']; ?>" />	
									</div>
							
								</div>
                                


								<div class="form-group">
									<label for="description" class="col-sm-3 control-label">
										<?php echo l('description'); ?>
									</label>
									<div class="col-sm-9">
										<textarea class="enter form-control des" rows='4' id="desc_<?php echo $room_type['id'] ?>" name="description" type="text" autocomplete="off" ><?php
																																											echo $room_type['description'];
																																											?></textarea>
                                        <?php //$textName = 'desription_'.$room_type['id']; 
										?>
										<?php //echo $this->ckeditor->editor($textName, $room_type['description']); 
										?>
									</div>
								</div>	
								
								<div class="col-md-12 image-group" id="<?php echo $room_type['image_group_id']; ?>">
									<div class="form-group">
										<label class="col-sm-3 control-label">
											<?php echo l('images'); ?> 
											
					    					
										</label>
										<div class="col-sm-9">
										<button 
													class="btn btn-primary btn-sm add-image"
													data-toggle="modal" 
													data-target="#image_edit_modal"
												>
													<?php echo l('Add Image', true); ?>
												</button><br/>
											<?php
										
											if (isset($room_type['images'])) :

												foreach ($room_type['images'] as $image) :
													$image_url = $this->image_url . $company_id . "/" . $image['filename'];
											?>
													   	<img 
													   		class="thumbnail col-md-3 add-image" 
													   		src="<?php echo $image_url; ?>" 
													   		title="<?php echo $image['filename']; ?>" 
													   		data-toggle="modal" 
													   		data-target="#image_edit_modal" 
													   	/>
													
										    <?php
												endforeach;
											endif;
											?>
										</div>
									</div>
								</div>	
								<div class="col-md-12 image-group" id="<?php echo $room_type['image_group_id']; ?>">
									<div class="form-inline form-group">
										<label class="col-sm-3 control-label">
										</label>
										<div class="col-sm-9">
											<!-- <button 
												class="btn btn-success update-room-type-button" count="<?php echo $count; ?>"
											>
												<?php echo l('update_room_type'); ?>
											</button> -->
										</div>
									</div>
								</div>
							</div>
						</div>
							
				<?php $count++;
                 ?>
                 	<?php else : ?>
			<h3>No Room Type(s) have been recorded</h3>
		<?php endif; ?>