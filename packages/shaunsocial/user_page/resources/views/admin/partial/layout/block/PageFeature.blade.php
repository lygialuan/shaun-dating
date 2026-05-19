<div class="form-group">
    <label class="control-label">{{__('Number of items to show')}}</label>
    <input id="item_number" class="form-control" name="item_number" value="10" type="number">
</div>
<script>
    function getPageFeature()
    {
        return {
            'item_number': $('#modal-ajax #item_number').val()
        }
    }
    function setPageFeature(data)
    {
        var number = 10
        if (typeof data.item_number !== "undefined") {
            number = data.item_number;
        }
        $('#modal-ajax #item_number').val(number)
    }
</script>