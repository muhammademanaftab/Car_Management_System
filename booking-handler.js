document.addEventListener('DOMContentLoaded', function() {
    const carId = new URLSearchParams(window.location.search).get('car_id');    
    async function getExistingReservations() {
        try {
            const response = await fetch(`get_reservations.php?car_id=${carId}`);
            const data = await response.json();
            const reservationsArray = Object.values(data).filter(
                reservation => reservation.car_id.toString() === carId
            );
            return reservationsArray;
        } catch (error) {
            console.error('Error fetching reservations:', error);
            return [];
        }
    }

    async function initializeDatePicker() {
        const reservations = await getExistingReservations();
        const disabledRanges = reservations.map(reservation => ({
            from: reservation.start_date,
            to: reservation.end_date
        }));

        const commonConfig = {
            minDate: "today",
            disable: disabledRanges,
            dateFormat: "Y-m-d",
            mode: "single"
        };

        const fromPicker = flatpickr("#from_date", {
            ...commonConfig,
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    untilPicker.set('minDate', selectedDates[0]);
                    
                    const untilDate = untilPicker.selectedDates[0];
                    if (untilDate && untilDate <= selectedDates[0]) {
                        untilPicker.clear();
                    }
                }
            }
        });

        const untilPicker = flatpickr("#until_date", {
            ...commonConfig,
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    fromPicker.set('maxDate', selectedDates[0]);
                    
                    const fromDate = fromPicker.selectedDates[0];
                    if (fromDate && fromDate >= selectedDates[0]) {
                        fromPicker.clear();
                    }
                }
            }
        });

        window.fromPicker = fromPicker;
        window.untilPicker = untilPicker;
    }

    const bookingForm = document.querySelector('form');
    bookingForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        try {
            const response = await fetch('process_booking.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showModal({
                    title: 'Booking Confirmed!',
                    message: result.message,
                    details: result.details
                });
                initializeDatePicker();
            } else {
                showModal({
                    title: 'Booking Error',
                    message: result.message,
                    isError: true
                });
            }
        } catch (error) {
            showModal({
                title: 'Error',
                message: 'An error occurred while processing your booking.',
                isError: true
            });
        }
    });

    function showModal(data) {
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        
        const modalContent = `
            <div class="modal-content ${data.isError ? 'error' : 'success'}">
                <h3>${data.title}</h3>
                <p>${data.message}</p>
                ${data.details ? `
                    <div class="booking-details">
                        <h4>Booking Details</h4>
                        <p><strong>Car:</strong> ${data.details.car_name}</p>
                        <p><strong>From:</strong> ${data.details.start_date}</p>
                        <p><strong>Until:</strong> ${data.details.end_date}</p>
                    </div>
                ` : ''}
                <button onclick="closeModal(this)">Close</button>
            </div>
        `;
        
        modal.innerHTML = modalContent;
        document.body.appendChild(modal);
        
        if (!data.isError) {
            setTimeout(() => {
                closeModal(modal.querySelector('button'));
            }, 5000);
        }
    }

    window.closeModal = function(button) {
        const modal = button.closest('.custom-modal');
        modal.classList.add('fade-out');
        setTimeout(() => {
            modal.remove();
        }, 500);
    };

    initializeDatePicker();
});