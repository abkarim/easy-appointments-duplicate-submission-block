/**
 * Get ea elements
 */
const ea_container = document.querySelector("body .ea-bootstrap");
const ea_form = ea_container.querySelector("form");
const submit_button = ea_form.querySelector(".booking-button.ea-btn");
const email_element = ea_form.querySelector("#email");
const phone_element = ea_form.querySelector("#phone");
const booking_overview_element = ea_form.querySelector("#booking-overview");

/**
 * Check if user already already submitted appointment
 */
async function should_allow_form_submission(formData = {}) {
    const response = await fetch(
        `${plugin_info_from_backend.ajax_url}?action=${encodeURIComponent(
            "easy_appointments_duplicate_submission_block__should_allow_submission"
        )}`,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": plugin_info_from_backend.ajax_nonce,
            },
            credentials: "same-origin",
            body: JSON.stringify(formData),
        }
    );

    if (response.ok) {
        const body = await response.json();
        if (body.data.success === true && body.data.data === true) return true;
        return false;
    }

    return false;
}

/**
 * Hide main form button
 */
submit_button.style.display = "none";

/**
 * Create a fake submit button
 * and add to the same position
 */
const fakeButton = document.createElement("button");
fakeButton.type = "button";
fakeButton.className = submit_button.className;
fakeButton.textContent = submit_button.textContent;

// Append as a first child
submit_button.parentElement.prepend(fakeButton);

let messageContainer = null;

/**
 * Handle submit click
 */
fakeButton.addEventListener("click", async () => {
    if (messageContainer !== null) {
        messageContainer.remove();
    }

    // Disable button
    fakeButton.disabled = true;

    const data = {
        email: email_element.value,
        phone: phone_element.value,
    };

    const allow = await should_allow_form_submission(data);

    if (allow) {
        submit_button.click();
    } else {
        fakeButton.disabled = false;

        /**
         * Create error message container
         */
        messageContainer = document.createElement("p");
        messageContainer.style.color = "red";
        messageContainer.style.fontSize = "1.2rem";
        messageContainer.style.width = "100%";
        messageContainer.style.whiteSpace = "break-spaces";
        messageContainer.className = "ea_appointments_block_error_message";

        messageContainer.textContent =
            "You have already booked an appointment, please wait at least " +
            plugin_info_from_backend.days_duration +
            " days from the date of booking";

        booking_overview_element.appendChild(messageContainer);
    }
});
