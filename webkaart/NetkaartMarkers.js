L.MastIconMarker = L.CircleMarker.extend({

    initialize: function(latlng, options) {
        this.voltage = parseFloat(options.voltage);
        this.name = options.name;
        L.CircleMarker.prototype.initialize.call(this, latlng, {
            renderer: options.renderer,
            stroke: false,
            fill: false,
            hover: false,
            radius: 10,
            zIndex: 200
        });
    },

    _getStyle: function (voltage) {
        var x = xmlDoc.getElementsByTagName("Spanning");
        for (i = 0; i <x.length; i++) {
            if (voltage >= parseFloat(x[i].getAttribute("LaagGrens")) && voltage <= parseFloat(x[i].getAttribute("HoogGrens"))) {
                var stijl = xmlDoc.getElementsByTagName("Masticoon");
                return {
                    icon: {
                        color: "#" + stijl[i].getElementsByTagName("Icoon")[0].getAttribute("Color"),
                        size: Math.ceil(stijl[i].getElementsByTagName("Icoon")[0].getAttribute("Size"))
                    },
                    label: {

                    }
                }
            }
        }
    },

    _updatePath: function () {
        if (!this._renderer._drawing || this._empty()) {
            return;
        }

        var p = this._point;
        var ctx = this._renderer._ctx;

        this._renderer._layers[this._leaflet_id] = this;

        var style = this._getStyle(this.voltage);
        console.log(this.voltage, style);

        var size = 1.8 * style.icon.size;

        ctx.globalAlpha = 1;
        ctx.beginPath();
        ctx.arc(p.x, p.y, 3 * size, 0, Math.PI * 2);
        ctx.lineWidth = 0;
        ctx.fillStyle = '#000000';
        ctx.fill();

        ctx.beginPath();
        ctx.arc(p.x, p.y, 2 * size, 0, Math.PI * 2);
        ctx.lineWidth = 0;
        ctx.fillStyle = style.icon.color;
        ctx.fill();

        ctx.beginPath();
        ctx.arc(p.x, p.y, size, 0, Math.PI * 2);
        ctx.lineWidth = 0;
        ctx.fillStyle = '#000000';
        ctx.fill();

        this._renderer._fillStroke(ctx, this);
    }
});
