/* global Vue $ data ajaxurl */

let app;
let column;
let visible = false;
const id = 'license-settings-' + data.slug;

const settingsButton = $('a#enter-license-' + data.slug).click(function(e) {
    e.preventDefault();
    
    if (visible)
        $('#' + id).css('display', 'none');
    else
        $('#' + id).css('display', 'table-row');

    visible = !visible;
});

if (!data.active)
    settingsButton
        .text(data.translations['Enter License'])
        .css('color', '#3db634');
else
    settingsButton
        .text(data.translations['License Settings'])
        .css('color', '');

const rootElement = settingsButton.closest('tr');
column = $('<tr class="active" id="' + id + '"></tr>').html(
    '<th class="check-column"></th><td><license-settings></license-settings></td><td></td>'
);
column.css('display', 'none');
rootElement.after(column);

Vue.mixin({
    computed: {
        $data: () => data
    }
});

Vue.component('deactivation-view', {
    template: `
        <div>
            <span style="font-size: 15px; font-weight: 600;margin-bottom: 10px;display: block;">Deactivate Plugin</span>

            <p>{{ $data.translations['Are you sure you want to deactivate the plugin? This will free up the license to be used on a different site.'] }}</p>
            <button class="button-primary">{{ $data.translations['Deactivate'] }}</button>
        </div>
    `
});

Vue.component('activation-view', {
    template: `
        <div>
            <span style="font-size: 15px; font-weight: 600;margin-bottom: 10px;display: block;">Activate Plugin</span>

            <label style="display: block;">{{ $data.translations['License or Envato Purchase Code'] }}</label>
            <input style="display: block; width: 100%; margin-bottom: 10px;" type="text" v-model="license" :placeholder="$data.translations['Enter License or Envato Purchase Code']" />

            <label style="display: block;">
                <input style="float: left;margin-top: 0px;" type="checkbox" v-model="consent"/>
                <span style="font-size: 11px;display: block;margin: 10px 0 10px 27px">
                    {{ $data.translations['I allow the following data to be sent to our update servers: license key, site url, WordPress version, PHP version and package version. This data is required to provide license activation and update functionality.'] }}
                </span>
            </label>

            <button class="button-primary" @click.prevent="activate">{{ $data.translations['Activate'] }}</button>
        </div>
    `,
    data: () => ({
        license: '',
        consent: false
    }),
    methods: {
        activate() {
            if (this.license === '') {
                alert(this.$data.translations['Please provide a license key.']);
                return;
            }

            if (!this.consent) {
                alert(this.$data.translations['To use the extended funcionality of this plugin, you need to allow the required data to be sent to our servers. Don\'t worry, we don\'t share that data with anyone. But it is required to verify an activated license.']);
                return;
            }

            this.$emit('startLoading');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'wpls_activate_' + this.$data.slug,
                    license_key: this.license
                }
            })
            .done(response => {
                this.$emit('stopLoading');

                if (response.activated) {
                    this.$emit('activated');
                    alert('The plugin was successfully activated!');
                } else {
                    alert(response.error.message);
                }
            })
            .fail(response => {
                this.$emit('stopLoading');

                alert(
                    'An error occurred in your WordPress instance while processing the license activation event.'
                );
            });
        }
    }
});

Vue.component('settings-view', {
    template: `
        <div>
            <div v-if="loading">
                <div style="display: flex; height: 70px;">
                    <div style="display: flex; flex: 0 0 100%; justify-content: center;align-items: center;">
                        <!--<span class="dashicons dashicons-update" style="font-size: 50px; color: rgba(0, 0, 0, 0.2);transform: translateX(-13px);will-change: transform;"></span>-->
                        <span>Loading...</span>
                    </div>
                </div>
                
            </div>
            <div v-show="!loading">
                <div style="display: flex; height: 70px;" v-if="site === 'overview' && !active">
                    <div style="display: flex; flex: 0 0 100%; justify-content: center;align-items: center; /*border-right: 1px solid rgba(0, 0, 0, 0.2);*/">
                        <button class="button-primary" @click.prevent="activate">Activate</button>
                    </div>
                    <!--<div style="display: flex; flex: 0 0 50%; justify-content: center;align-items: center;">
                        <button class="button" @click.prevent="deactivate">Deactivate</button>
                    </div>-->
                </div>

                <div style="display: flex; height: 70px;" v-if="site === 'overview' && active">
                    <div style="display: flex; flex: 0 0 100%; justify-content: center;align-items: center; /*border-right: 1px solid rgba(0, 0, 0, 0.2);*/">
                        <button class="button-primary" @click.prevent="deactivate">Deactivate</button>
                    </div>
                    <!--<div style="display: flex; flex: 0 0 50%; justify-content: center;align-items: center;">
                        <button class="button" @click.prevent="deactivate">Deactivate</button>
                    </div>-->
                </div>

                <activation-view v-if="site === 'activation'" @activated="onActivation" @startLoading="startLoading" @stopLoading="stopLoading"></activation-view>
            </div>
        </div>
    `,
    data() {
        return {
            site: 'overview',
            active: false,
            loading: false
        };
    },
    beforeMount() {
        this.active = this.$data.active;
    },
    methods: {
        startLoading() {
            this.loading = true;
        },
        stopLoading() {
            this.loading = false;
        },
        activate() {
            this.site = 'activation';
        },
        deactivate() {
            this.startLoading();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'wpls_deactivate_' + this.$data.slug
                }
            })
            .done(response => {
                this.stopLoading();

                if (response.deactivated) {
                    this.active = false;

                    settingsButton
                        .text(this.$data.translations['Enter License'])
                        .css('color', '#3db634');

                    alert(
                        'The plugin was successfully deactivated! The license can now be used on another site.'
                    );
                } else {
                    alert(response.error.message);
                }
            })
            .fail(response => {
                this.stopLoading();

                alert(
                    'An error occurred in your WordPress instance while processing the license deactivation event.'
                );
            });
        },

        onActivation() {
            this.site = 'overview';
            this.active = true;

            settingsButton
                .text(this.$data.translations['License Settings'])
                .css('color', '');
        }
    }
});

Vue.component('license-settings', {
    template: `
        <div>
            <h2 style="display: inline-block; margin-top: 0px;">
                {{ $data.name }}
                <span style="font-weight: 400;">{{ $data.translations['License Settings'] }}</span>
            </h2>
            <a href="#" style="font-weight: 400; float: right; font-size: 12px;" @click.prevent="help">{{ $data.translations['What\\\'s this?'] }}</a>

            <settings-view></settings-view>        
        </div>
    `,
    methods: {
        help() {
            alert(this.$data.translations['To enable full functionality of this plugin, all you have to do is to enter the license that was provided to you during sale. If you bought the plugin using the Envato market, you\'ll need to enter the Envato purchase code.']);
        }
    }
});

app = new Vue({
    el: '#' + id
});
