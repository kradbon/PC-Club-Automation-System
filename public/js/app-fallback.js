document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('[data-menu-toggle]');
    const nav = document.querySelector('[data-main-nav]');
    menuButton?.addEventListener('click', () => {
        const expanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!expanded));
        nav?.classList.toggle('open', !expanded);
    });

    const languageButton = document.querySelector('[data-language-toggle]');
    const languageOptions = document.querySelector('[data-language-options]');
    languageButton?.addEventListener('click', () => {
        const expanded = languageButton.getAttribute('aria-expanded') === 'true';
        languageButton.setAttribute('aria-expanded', String(!expanded));
        languageOptions?.classList.toggle('open', !expanded);
    });

    const foodMethod = document.querySelector('[data-food-fulfillment]');
    const deliveryFields = document.querySelector('[data-food-delivery-fields]');
    const clubFields = document.querySelector('[data-food-club-fields]');
    const deliveryAddress = document.querySelector('[data-food-address]');
    const deliveryPhone = document.querySelector('[data-food-phone]');
    const foodEstimate = document.querySelector('[data-food-estimate]');
    const foodCheckout = document.querySelector('[data-food-checkout]');
    const updateFoodFields = () => {
        const delivery = foodMethod?.value === 'delivery';
        if (deliveryFields) deliveryFields.hidden = !delivery;
        if (clubFields) clubFields.hidden = delivery;
        if (deliveryAddress) deliveryAddress.required = Boolean(delivery);
        if (deliveryPhone) deliveryPhone.required = Boolean(delivery);
    };
    foodMethod?.addEventListener('change', updateFoodFields);
    updateFoodFields();
    const updateFoodEstimate = () => {
        if (!foodEstimate || !foodCheckout) return;
        const subtotal = [...document.querySelectorAll('[data-unit-price]')].reduce((sum, input) => {
            const quantity = Math.max(0, Math.min(12, Number.parseInt(input.value || '0', 10) || 0));
            return sum + (Number.parseInt(input.dataset.unitPrice || '0', 10) * quantity);
        }, 0);
        const deliveryFee = foodMethod?.value === 'delivery'
            ? Number.parseInt(foodCheckout.dataset.deliveryFeeCents || '0', 10)
            : 0;
        const amount = new Intl.NumberFormat(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}).format((subtotal + deliveryFee) / 100);
        foodEstimate.textContent = `${foodEstimate.dataset.label}: ${foodCheckout.dataset.currency} ${amount}`;
    };
    document.querySelectorAll('[data-unit-price]').forEach(input => input.addEventListener('input', updateFoodEstimate));
    foodMethod?.addEventListener('change', updateFoodEstimate);
    updateFoodEstimate();

    const zone = document.querySelector('[name="pc_zone_id"]');
    const pc = document.querySelector('[name="pc_id"]');
    const time = document.querySelector('[name="starts_at"]');
    const duration = document.querySelector('[name="duration_minutes"]');
    const status = document.querySelector('[x-text="status"]');
    const loadAvailability = async () => {
        if (!zone || !pc || !time || !duration) return;
        const selected = zone.options[zone.selectedIndex];
        pc.replaceChildren(new Option('Select a PC', ''));
        pc.required = selected?.dataset.mode !== 'private_room';
        pc.disabled = selected?.dataset.mode === 'private_room';
        if (!zone.value || pc.disabled || !time.value) return;
        if (status) status.textContent = 'Checking PC availability…';
        const url = new URL(document.querySelector('[name="_token"]')?.form?.action?.replace(/\/bookings$/, '/booking-availability') || location.pathname.replace(/\/bookings.*/, '/account/booking-availability'), location.origin);
        url.searchParams.set('zone_id', zone.value);
        url.searchParams.set('starts_at', time.value);
        url.searchParams.set('duration_minutes', duration.value);
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            (data.pcs || []).forEach(item => pc.add(new Option(item.name, item.id)));
            if (status) status.textContent = data.pcs?.length ? 'Available PCs updated.' : 'No PCs are available at that time.';
        } catch (_) {
            if (status) status.textContent = 'Availability is verified again when staff confirms your request.';
        }
    };
    [zone, time, duration].forEach(input => input?.addEventListener('change', loadAvailability));
    if (zone?.value) loadAvailability();
});
