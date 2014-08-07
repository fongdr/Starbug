define(["dojo/_base/Deferred", "starbug/grid/Grid", "dgrid/extensions/DnD"], function(Deferred, Grid, DnD){
	var DnDGrid = dojo.declare('starbug.grid.MemoryGrid', [Grid, DnD], {
		orderColumn:'position',
		dndParams:{
			withHandles:true,
			onDropInternal: function(nodes, copy, targetItem) {
				
					var grid = this.grid, targetRow, targetPosition, fromRow, fromPosition;

					if (!this._targetAnchor) return
					
					//get target position
					targetRow = grid.row(this._targetAnchor);
					targetPosition = grid.store.data.indexOf(targetRow.data);
					
					//get source position
					fromRow = grid.row(nodes[0]);
					fromPosition = grid.store.data.indexOf(fromRow.data);					
					
					console.log(targetPosition);
					console.log(fromPosition);
					console.log(nodes.length);
					
					//pull out the movers
					var movers = grid.store.data.splice(fromPosition, nodes.length);
					
					//put them back at the new position
					movers.unshift(0);
					if (targetPosition > fromPosition) {
						movers.unshift(targetPosition + 1 - nodes.length);
					} else {
						movers.unshift(targetPosition);
					}
					grid.store.data.splice.apply(grid.store.data, movers);
					grid.refresh();
					
					if (grid.editor) grid.editor.refresh();
			
			}
		}
	});
	return DnDGrid;
});
