import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

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
});
