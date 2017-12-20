<div class="row">
    <div class="col-md-12">
        <h3 class="description">map</h3>
    </div>
    <div class="col-12">
        <div class="box-body">
            <div id="map" style="width: 100%; height: 800px"></div>
        </div>
    </div>
</div>

@section('scripts')
<script type="text/javascript">
    let opts = {
        features: {!! json_encode($coords['features']); !!},

    };

    phpvms.render_route_map(opts);
</script>
@endsection
