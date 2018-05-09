<?php

namespace Smoolabs\V1;

if (!class_exists('\\Smoolabs\\V1\\LicenseSettings', false)) :

class LicenseSettings
{
    protected $name;
    protected $slug;

    public function __construct($name, $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
    }

    public function enqueueScript($license)
    {
        $translations = array(
            'Enter License' => __('Enter License', 'smoolabs-updater'),
            'License or Envato Purchase Code' => __('License or Envato Purchase Code', 'smoolabs-updater'),
            'License Settings' => __('License Settings', 'smoolabs-updater'),
            'Enter License or Envato Purchase Code' => __('Enter License or Envato Purchase Code', 'smoolabs-updater'),
            'Save' => __('Save', 'smoolabs-updater'),
            'Activate' => __('Activate', 'smoolabs-updater'),
            'Plugin successfully activated!' => __('Plugin successfully activated!', 'smoolabs-updater'),
            'Plugin could not be activated. The license key is invalid.' => __('Plugin could not be activated. The license key is invalid.', 'smoolabs-updater'),
            'Plugin could not be activated. An unknown error occurred.' => __('Plugin could not be activated. An unknown error occurred.', 'smoolabs-updater'),
            'Enter License' => __('Enter License', 'smoolabs-updater'),
            'Enter License' => __('Enter License', 'smoolabs-updater'),
            'Enter License' => __('Enter License', 'smoolabs-updater'),
        );

        $data = array(
            'translations' => $translations,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'license'      => $license,
        );

        ?>
        <script type="text/javascript">
            window['WPUpdater_<?php echo $this->slug; ?>'] = (function($, data) {
                
                var settingsButton = $('a#enter-license-' + slug);
                var rootElement = settingsButton.closest('tr');
                var state = {
                    license: null,
                    loading: false
                };

                function render(state) {
                    if (!license)
                        settingsButton
                            .text(data.translations['Enter License'])
                            .css('color', '#3db634');
                    else
                        settingsButton
                            .text(data.translations['License Settings'])
                            .css('color', '');

                    var column = $('<td></td>')
                        .css('padding', '20px');

                    var title = $('<h2></h2>')
                        .css('margin-top', '0px')
                        .css('display', 'inline-block')
                        .text(data.name)
                        .append(
                            $('<span></span>')
                                .css('font-weight', 400)
                                .text(data.translations['License Settings']);
                        );

                    var helpButton = $('a')
                        .attr('href', '#')
                        .css('font-weight', 400)
                        .css('float', 'right')
                        .css('font-size', '12px')
                        .text(data.translations['What\'s this?']);

                    var label = $('<strong></strong>')
                        .text(data.translations['License or Envato Purchase Code'])
                        .append($('<br>'));

                    var licenseInput = $('<input />')
                        .css('width', '100%')
                        .attr('type', 'text')
                        .attr('value', state.license)
                        .attr('placeholder', data.translations['Enter License or Envato Purchase Code']);

                    var saveButton = $('<button></button>')
                        .addClass('button-primary')
                        .css('width', '150px')
                        .css('margin-top', '10px')
                        .text(
                            !!license
                                ? data.translations['Save']
                                : data.translations['Activate']
                        )
                        .click(onSaveButtonClick);

                    if (state.loading) {
                        licenseInput
                            .attr('disabled', 'disabled');

                        saveButton
                            .attr('disabled', 'disabled');

                        column
                            .css('opacity', '0.2')
                            .css('pointer-events', 'none');
                    }
                }

            function onSaveButtonClick(event) {
                event.preventDefault();

                checkLicense(data.slug, state.license, function(success, invalidLicense) {
                    state.loading = false;
                    render(state);

                    if (success) {
                        alert(data.translations['Plugin successfully activated!']);
                    } else if (!success && invalidLicense) {
                        alert(data.translations['Plugin could not be activated. The license key is invalid.']);
                    } else {
                        alert(data.translations['Plugin could not be activated. An unknown error occurred.']);
                    }
                });

                state.loading = true;
                render(state);
            }

            function checkLicense(slug, license, callback) {
                jQuery
                    .ajax({
                        type: 'post',
                        url: ajaxurl,
                        data: {
                            action: 'mpu_save_license_' + slug,
                            license_key: license
                        }
                    })
                    .done(function(data) {
                        callback(true, !!data.valid);
                    })
                    .fail(function() {
                        callback(false, false);
                    });
            }

            })(jQuery, <?php echo json_encode($data); ?>);
        </script>
        <?
    }
}

endif;