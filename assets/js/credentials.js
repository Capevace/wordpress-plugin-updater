var mateffyPluginUpdater100 = (function() {
	var licenses = {};

	function setupLicenseUI(config) {
		if (!licenses[config.slug])
			licenses[config.slug] = config.license || '';

		var licenseBtn = jQuery(
			'a#enter-license-' + config.slug
		);

		var row = licenseBtn.closest('tr');
		var editRow = null;

		licenseBtn.click(function(e) {
			e.preventDefault();

			if (editRow) {
				editRow.remove();
				editRow = null;
			}

			editRow = jQuery('<tr></tr>');
			row.after(editRow);

			var container = jQuery('<td></td>');
			var heading = jQuery('<strong>Enter License<br></strong>');

			var input = jQuery('<input />')
				.attr('type', 'text')
				.attr('value', licenses[config.slug])
				.attr('placeholder', 'Enter License');
			var btn = jQuery('<button></button>')
				.text('Save')
				.click(function(e) {
					e.preventDefault();

					input.attr('disabled', 'disabled');
					btn.attr('disabled', 'disabled');

					var license = input.val();
					checkLicense(config, license, function(valid, empty) {
						if (empty) {
							saveLicense(config, '', function(success) {
								if (success) {
									licenses[config.slug] = '';
									editRow.remove();
									editRow = null;

									licenseBtn
										.text('Enter License')
										.css('color', '#3db634');
								}
							});

							return;
						}

						if (!valid) {
							alert('That License is invalid.');
							input.removeAttr('disabled');
							btn.removeAttr('disabled');
						} else {
							saveLicense(config, license, function(success) {
								if (success) {
									licenses[config.slug] = license;
									editRow.remove();
									editRow = null;

									licenseBtn
										.text('License Settings')
										.css('color', '');
								}
							});
						}
					});
				});

			container.append(heading);
			container.append(input);
			container.append(btn);

			editRow.append(jQuery('<td></td>')).append(container);
		});
	}

	function checkLicense(config, license, callback) {
		if (!license || license === '') {
			callback(true, true);
			return;
		}

		jQuery
			.getJSON(ajaxurl, {
				action: 'mpu_validate_license_' + config.slug,
				license_key: license
			})
			.done(function(data) {
				callback(!!data.valid, false);
			})
			.fail(function() {
				callback(false, false);
			});
	}

	function saveLicense(config, license, callback) {
		jQuery
			.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'mpu_save_license_' + config.slug,
					license_key: license
				}
			})
			.done(function() {
				alert('License was successfully saved!');
				callback(true);
			})
			.fail(function() {
				alert('License could not be saved!');
				callback(true);
			});
	}

	return {
		setupLicenseUI
	};
})();
