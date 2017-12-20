const phpvms= (function() {

    const draw_base_map = (opts) => {

        opts = _.defaults(opts, {
            render_elem: 'map',
            zoom: 12,
            layers: [],
            set_marker: false,
        });

        /*var openaip_airspace_labels = new L.TileLayer.WMS(
            "http://{s}.tile.maps.openaip.net/geowebcache/service/wms", {
                maxZoom: 14,
                minZoom: 12,
                layers: 'openaip_approved_airspaces_labels',
                tileSize: 1024,
                detectRetina: true,
                subdomains: '12',
                format: 'image/png',
                transparent: true
            });

        openaip_airspace_labels.addTo(map);*/

        const opencyclemap_phys_osm = new L.TileLayer(
            'http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey=f09a38fa87514de4890fc96e7fe8ecb1', {
                maxZoom: 14,
                minZoom: 4,
                format: 'image/png',
                transparent: true
            });

        const openaip_cached_basemap = new L.TileLayer("http://{s}.tile.maps.openaip.net/geowebcache/service/tms/1.0.0/openaip_basemap@EPSG%3A900913@png/{z}/{x}/{y}.png", {
            maxZoom: 14,
            minZoom: 4,
            tms: true,
            detectRetina: true,
            subdomains: '12',
            format: 'image/png',
            transparent: true
        });

        const openaip_basemap_phys_osm = L.featureGroup([opencyclemap_phys_osm, openaip_cached_basemap]);

        let map = L.map('map', {
            layers: [openaip_basemap_phys_osm],
            zoom: opts.zoom,
            scrollWheelZoom: false,
        });

        const attrib = L.control.attribution({position: 'bottomleft'});
        attrib.addAttribution("<a href=\"https://www.thunderforest.com\" target=\"_blank\" style=\"\">Thunderforest</a>");
        attrib.addAttribution("<a href=\"https://www.openaip.net\" target=\"_blank\" style=\"\">openAIP</a>");
        attrib.addAttribution("<a href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\" style=\"\">OpenStreetMap</a> contributors");
        attrib.addAttribution("<a href=\"https://www.openweathermap.org\" target=\"_blank\" style=\"\">OpenWeatherMap</a>");

        attrib.addTo(map);

        return map;
    };

    return {

        /**
         *
         * @param opts
         */
        render_route_map: (opts) => {

            opts = _.defaults(opts, {
                features: null,   // [ {name, lat, lon}, {name, lat, lon} ];
                center: [],
                render_elem: 'map',
                overlay_elem: '',
                zoom: 5,
                layers: [],
                set_marker: false,
            });

            console.log(opts.features);

            let map = draw_base_map(opts);

            var geodesicLayer = L.geodesic([], {
                weight: 7,
                opacity: 0.5,
                color: '#ff33ee',
                steps: 50,
                wrap: false,
            }).addTo(map);

            geodesicLayer.geoJson(opts.features)
            map.fitBounds(geodesicLayer.getBounds());

            /*let route = L.geoJSON(opts.features, {
                "color": "#ff7800",
                "weight": 5,
                "opacity": 0.65
            });*/

            //map.setView(opts.center, opts.zoom);
            //map.fitBounds(route.getBounds());
            //route.addTo(map)
        },

        /**
         * Render a map with the airspace, etc around a given set of coords
         * e.g, the airport map
         * @param opts
         */
        render_airspace_map: (opts) => {
            opts = _.defaults(opts, {
                render_elem: 'map',
                overlay_elem: '',
                lat: 0,
                lon: 0,
                zoom: 12,
                layers: [],
                set_marker: false,
            });

            let map = draw_base_map(opts);
            const coords = [opts.lat, opts.lon];
            console.log('Applying coords', coords);

            map.setView(coords, opts.zoom);
            if (opts.set_marker === true) {
                L.marker(coords).addTo(map);
            }

            return map;
        }
    }
})();
