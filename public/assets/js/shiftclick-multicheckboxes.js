let lastChecked;

document.body.addEventListener('click', function (e) {
    // Check if the clicked element is a checkbox
    if (e.target && e.target.type === 'checkbox') {
        let checkbox = e.target;

        if (e.shiftKey && lastChecked && !lastChecked.disabled) {
            let checkboxes = Array.from(document.querySelectorAll('input[type=checkbox]'));
            let start = checkboxes.indexOf(lastChecked);
            let end = checkboxes.indexOf(checkbox);

            // Adjust the indices to ensure start is less than end
            if (start > end) {
                [start, end] = [end, start];
            }

            // Loop through the checkboxes
            for (let i = start; i <= end; i++) {
                if (checkboxes[i].disabled) continue; // Exit if the checkbox is disabled

                checkboxes[i].checked = lastChecked.checked;

                let event = new Event('change', { bubbles: true });
                checkboxes[i].dispatchEvent(event); // Trigger change event
            }
        }

        lastChecked = checkbox; // Store the last checked checkbox
    }
});
