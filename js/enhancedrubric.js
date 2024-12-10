M.gradingform_enhancedrubric = {};

/**
 * This function is called for each enhanced rubric on page.
 */
M.gradingform_enhancedrubric.init = function(Y, options) {
    Y.on('click', M.gradingform_enhancedrubric.levelclick, '.gradingform_enhancedrubric.editable .level', null, Y, options.name);
    // Capture also space and enter keypress.
    Y.on('key', M.gradingform_enhancedrubric.levelclick, '.gradingform_enhancedrubric.editable .level', 'space', Y, options.name);
    Y.on('key', M.gradingform_enhancedrubric.levelclick, '.gradingform_enhancedrubric.editable .level', 'enter', Y, options.name);

    Y.all('.gradingform_enhancedrubric .radio').setStyle('display', 'none')
    // With marking workflow enabled, some sections may be frozen depending on the users capabilities.
    Y.all('.gradingform_enhancedrubric.editable .level').each(function (node) {
        if (node.one('input[type=radio]').get('checked')) {
            node.addClass('checked');
        }
    });

    Y.on('click', M.gradingform_enhancedrubric.copyclick, '.gradingform_enhancedrubric .copy', null, Y, options.name);
    // Capture also space and enter keypress.
    Y.on('key', M.gradingform_enhancedrubric.copyclick, '.gradingform_enhancedrubric .copy', 'space', Y, options.name);
    Y.on('key', M.gradingform_enhancedrubric.copyclick, '.gradingform_enhancedrubric .copy', 'enter', Y, options.name);
};

M.gradingform_enhancedrubric.levelclick = function(e, Y, name) {
    var el = e.target
    while (el && !el.hasClass('level')) el = el.get('parentNode')
    if (!el) return
    e.preventDefault();
    el.siblings().removeClass('checked');

    // Set aria-checked attribute for siblings to false.
    el.siblings().setAttribute('aria-checked', 'false');
    var chb = el.one('input[type=radio]')
    if (!chb.get('checked')) {
        chb.set('checked', true)
        el.addClass('checked')
        // Set aria-checked attribute to true if checked.
        el.setAttribute('aria-checked', 'true');
    } else {
        el.removeClass('checked');
        // Set aria-checked attribute to false if unchecked.
        el.setAttribute('aria-checked', 'false');
        el.get('parentNode').all('input[type=radio]').set('checked', false)
    }
}

M.gradingform_enhancedrubric.copyclick = function(e, Y, name) {
    var btnel = e.target;
    var copyfrom = btnel.getAttribute('data-copyfrom');
    var copyto = btnel.getAttribute('data-copyto');

    // Clear the existing values.
    Y.all('#enhancedrubric-advancedgrading' + copyto + ' .level.checked').each(function (node) {
        var id = node.getAttribute('id');
        var newid = id.replace('advancedgrading-section-' + copyfrom + '-criteria', 'advancedgrading-section-' + copyto + '-criteria');
        var el = Y.one('#' + newid);
        el.removeClass('checked');
        // Set aria-checked attribute for siblings to false.
        el.setAttribute('aria-checked', 'false');
    });

    // Copy the checked values.
    Y.all('#enhancedrubric-advancedgrading' + copyfrom + ' .level.checked').each(function (node) {
        var id = node.getAttribute('id');
        var newid = id.replace('advancedgrading-section-' + copyfrom + '-criteria', 'advancedgrading-section-' + copyto + '-criteria');
        var el = Y.one('#' + newid);
        var chb = el.one('input[type=radio]');
        chb.set('checked', true)
        el.addClass('checked')
        // Set aria-checked attribute to true if checked.
        el.setAttribute('aria-checked', 'true');
    });

    // Copy the comments.
    Y.all('#enhancedrubric-advancedgrading' + copyfrom + ' textarea').each(function (node) {
        var id = node.getAttribute('id');
        var el = Y.one('#' + id);
        var remark = el.get('value');
        var newid = id.replace('advancedgrading-section-' + copyfrom + '-criteria', 'advancedgrading-section-' + copyto + '-criteria');
        el = Y.one('#' + newid);
        el.set('value', remark);
    });
}
