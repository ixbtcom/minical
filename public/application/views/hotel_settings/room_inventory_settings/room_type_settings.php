

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-home text-success"></i>
            </div>
           Типы баннеров
        </div>
    </div>
  </div>



<div class="main-card mb-3 card">
    <div class="card-body">

<!-- Modal -->
<div class="modal fade" id="image_edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo l('edit_image'); ?></h4>
			</div>
			<div class="modal-body text-center" id="image_edit_modal_body">
			</div>
			<div class="modal-footer image-modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal"><?php echo l('close'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="col-sm-12">

	<div id="side-menu">
		<h5>
			<button id="add-room-type-button" class="btn btn-primary"><?php echo l('add_room_type'); ?></button>
		</h5>
		<!-- <div id="room-type-list"></div> -->
	</div>

	<div class="">
		<div>
		<div class="table-responsive">
			<table class="table table-hover rooms rate-plans-table">
				<thead>
					<tr>
						<th>
							Название
						</th>
						<th>
							<?php echo l('Acronym'); ?>
						</th>

					</tr>
				</thead>
				<tbody id="sortable">
					<?php
					if (isset($room_types)) :
						$count = 1;
						foreach ($room_types as $room_type) : ?>

							<tr class="booking-source-tr ui-sortable-handle room-type-div" id="<?php echo $room_type['id']; ?>">
								<td class="glyphicon_icon">
									<span class="grippy"></span>
									<!-- <input name="room-type-name" class="form-control"  value="<?php echo $room_type['name']; ?>" readonly /> -->
								 <?php echo $room_type['name']; ?>
								</td>
								<td><?php echo $room_type['acronym']; ?></td>
								<td style="width: 13%">
									<div class="btn-group pull-right" role="group">
										<button class="btn btn-sm btn-light edit_room_type" id="<?php echo $room_type['id'] ?>" data-min_occupancy="<?php echo $room_type['min_occupancy']; ?>" data-max_occupancy="<?php echo $room_type['max_occupancy']; ?>"><?php echo l('Edit'); ?></button>
										<button class="delete-room-type-button btn btn-sm btn-danger"><?php echo l('Delete'); ?>
										</button>
									</div>
								</td>
							</tr>
						<?php $count++;
						endforeach; ?>
					<?php else : ?>
						<h3>No Room Type(s) have been recorded</h3>
					<?php endif; ?>

				</tbody>
			</table>
					</div>

		<div class="modal fade" id="room_type_model" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" style="text-align: center;">
							<?php echo l('Edit Room Type', true); ?>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						<div class="col-md-12">

							<div class="form-group">
								<div class="text-right" style="float: right">
									<button 
												class="btn btn-success update-room-type-button" count="<?php echo $count; ?>"
											>
												<?php echo l('update_room_type'); ?>
											</button>
								</div>
							</div>
						</div>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->


		<!-- add new room type  model -->

		<div class="modal fade" id="addnew_room_type_model" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" style="text-align: center;">
							<?php echo l('Add Room Type', true); ?>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</h4>
					</div>
					<div class="modal-body">
					</div>
					<div class="modal-footer">
						<div class="col-md-12">

							<div class="form-group">
								<div class="col-sm-12 text-right" style="float: right">
									<button type="button" class="btn btn-danger" data-dismiss="modal">
										<?php echo l('Cancel', true); ?>
									</button>
									<button class="btn btn-success add_room_type" count="">
										<?php echo l('add_room_type'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		</div>
	</div>

</div></div></div>