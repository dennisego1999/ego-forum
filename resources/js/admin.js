document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            // Redirect to log in when the session expired
            if (status === 419) {
                window.location.href = '/admin';
                preventDefault();
                return;
            }

            // Bail outside testing environments
            if (['local', 'staging'].includes(window.filamentData.appEnv)) {
                return;
            }

            // Overwrite error handling
            if (status >= 500 && status < 600) {
                new FilamentNotification().title('Something has gone wrong.').danger().send();
                preventDefault();
            }
        });
    });
});
