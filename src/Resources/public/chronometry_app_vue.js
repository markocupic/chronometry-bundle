/*
 * This file is part of Chronometry Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license LGPL-3.0+
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/chronometry-bundle
 */
const chronometryApp = new Vue({
    el: '#chronometry-app',
    data: {
        isReady: false,
        isOnline: '',
        requestToken: '',
        modalId: null,
        currentTime: '',
        runners: null,
        categories: null,

        modal: {
            runnerIndex: null,
            runnerNumber: '',
            runnerFullname: '',
            runnerId: null,
            runnerIsFinisher: false,
            runnerdnf: false,
            lastChange: '',
            endTime: '',
        },
        searchForm: {
            showNumberDropdown: false,
            numberSuggests: [],
            showNameDropdown: false,
            nameSuggests: [],
        },
        stats: {
            total: 0,
            dispensed: 0,
            haveFinished: 0,
            running: 0,
            haveGivenUp: 0,
            runnersTotal: 0,
        }
    },

    created: function () {
        const self = this;
        self.requestToken = CHRONOMETRY.requestToken;
        self.modalId = CHRONOMETRY.modalId;

        window.setTimeout(function () {
            self.isReady = true;
        }, 2000);

        window.setInterval(function () {
            self.setTime();
        }, 1000);

        self.checkOnlineStatus();
        window.setInterval(function () {
            self.checkOnlineStatus();
        }, 15000);

        self.getDataAll();

        $(document).ready(function () {
            // Make table sortable
            $('#startlistTable').stupidtable();
        });
    },

    methods: {
        /**
         * Get all rows from server
         */
        getDataAll: function () {
            const self = this;
            const xhr = $.ajax({
                url: window.location.href + '?action=getDataAll',
                type: 'get',
                dataType: 'json',
            });
            xhr.done(function (response) {
                self.runners = response.runners;
                self.stats = response.stats;
                self.categories = response.categories;
            });
            xhr.fail(function () {
                alert("XHR-Request fehlgeschlagen!!!");
            });
            xhr.always(function () {
                //
            });
        },

        /**
         * Open modal
         * @param index
         */
        openModal: function (index) {
            const self = this;
            const runner = self.runners[index];
            const modal = self.modal;

            modal.runnerIndex = index;
            modal.runnerNumber = runner.number;
            modal.runnerFullname = runner.fullname;
            modal.runnerIsFinisher = runner.endtime !== '';
            modal.runnerHasNotice = runner.notice !== '';
            modal.runnerdnf = runner.dnf === '1';
            modal.runnerNotice = runner.notice.replace(/&\am\p;/g, '&');

            modal.runnerId = runner.id;

            const d = new Date(runner.tstamp * 1000);
            modal.lastChange = 'Letzte Änderung: ' + self.getFormatedTime(d);

            modal.endTime = runner.endtime === '' ? self.currentTime : runner.endtime;
            modal.endTime = runner.dnf === '1' ? '' : modal.endTime;

            document.getElementById('runnerDnfCtrl').checked = runner.dnf === '1';

            let modalElement = document.getElementById(self.modalId);

            modalElement.addEventListener('hidden.bs.modal', function () {
                $('html, body').animate({
                        //scrollTop: $('body').offset().top
                    }, 50, function () {
                        $('#searchNumber').val('').focus();
                        $('#searchName').val('');
                        self.searchForm.numberSuggests = [];
                        self.searchForm.nameSuggests = [];
                        self.searchForm.showNumberDropdown = false;
                        self.searchForm.showNameDropdown = false;
                    }
                );
            });

            modalElement.addEventListener('shown.bs.modal', function () {
                $('#endtimeCtrl').focus();
            });

            let bsModalWindow = bootstrap.Modal.getOrCreateInstance(modalElement, {
                keyboard: false
            });

            // Open modal
            bsModalWindow.show();

            // Clear Input Field
            $("#inputClear").click(function () {
                modal.endTime = '';
            });
        },

        /**
         * Scroll to number
         * @param event
         */
        scrollToNumber: function (event) {
            const self = this;

            const input = event.target;
            if ($(input).val() > 1) {
                const tr = $("tr[data-number='" + $(input).val() + "']");
                if ($(tr).length) {
                    $('html, body').animate({
                            scrollTop: $(tr).offset().top - 40
                        }, 400, function () {
                            // On animate end
                        }
                    );

                    // Open modal
                    const index = $(tr).data('index');
                    self.openModal(index);
                }
            }
        },

        /**
         * Save data to server
         */
        checkOnlineStatus: function () {
            const self = this;
            const xhr = $.ajax({
                url: window.location.href + '?action=checkOnlineStatus',
                type: 'get',
                dataType: 'json',
            });

            xhr.done(function (response) {
                self.isOnline = response.status === 'success';
            });

            xhr.fail(function () {
                self.isOnline = false;
            });

            xhr.always(function () {
                //
            });

        },

        /**
         * Remove non-natural numbers
         * @param event
         */
        validateNumberOnInput: function (event) {
            const inputEl = event.target;
            inputEl.value = inputEl.value.replace(/[^0-9]/g, '');
        },

        /**
         * Save data to server
         * @param index
         */
        saveRow: function (index) {
            const self = this;
            const runner = self.runners[index];
            const modal = self.modal;
            const id = modal.runnerId;
            const endtime = $('#endtimeCtrl').val();
            const dnf = $('.modal #runnerDnfCtrl').is(':checked') ? 1 : '';

            // Check for a valid input f.ex. 22:12:59
            const regex = /^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/;

            if (regex.test(endtime) || endtime === '') {

                // Close modal
                let modalElement = document.getElementById(self.modalId);

                let bsModalWindow = bootstrap.Modal.getOrCreateInstance(modalElement, {
                    keyboard: false
                });

                bsModalWindow.hide();

                // Fire xhr
                const xhr = $.ajax({
                    url: window.location.href + '?action=saveRow',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'REQUEST_TOKEN': self.requestToken,
                        'id': id,
                        'index': index,
                        'endtime': endtime,
                        'dnf': dnf
                    }
                });

                xhr.done(function (response) {
                    if (response.status === 'success') {
                        self.runners = response.runners;
                        self.stats = response.stats;
                        self.categories = response.categories;
                    } else {
                        alert('Fehler');
                    }
                });

                xhr.fail(function () {
                    alert("XHR-Request für id " + id + " fehlgeschlagen!!!");
                });
            } else {
                alert('Ungültige Eingabe: ' + endtime);
            }
        },

        /**
         * Set current time
         */
        setTime: function () {

            let currentTime = new Date();
            let h = currentTime.getHours();
            let m = currentTime.getMinutes();
            let s = currentTime.getSeconds();

            if (h < 10) {
                h = '0' + h;
            }

            if (m < 10) {
                m = '0' + m;
            }

            if (s < 10) {
                s = '0' + s;
            }

            this.currentTime = h + ":" + m + ":" + s;
        },

        /**
         * Set end time from current time
         */
        setEndTimeFromCurrentTime: function () {
            const self = this;
            const modal = self.modal;
            const d = new Date();
            modal.endTime = self.getFormatedTime(d);
        },

        /**
         * Clear end time
         */
        clearEndTime: function () {
            const self = this;
            self.modal.endTime = '';
        },

        /**
         * Show number dropdown
         * @param event
         */
        showNumberDropdownSuggest: function (event) {
            const self = this;
            const input = event.target;

            if ($(input).val() === '') {
                self.searchForm.numberSuggests = [];
                self.searchForm.showNumberDropdown = false;
                return;
            }
            // Clean name input
            $('#searchName').val('');
            self.searchForm.nameSuggests = [];
            self.searchForm.showNameDropdown = false;

            const rows = $('#startlistTable tbody tr');

            const regex = new RegExp('^' + $(input).val() + '(.*)', 'i');

            self.searchForm.numberSuggests = [];
            rows.each(function () {
                if (regex.test($(this).attr('data-number'))) {

                    const runner = {
                        index: $(this).attr('data-index'),
                        number: $(this).attr('data-number'),
                        fullname: $(this).attr('data-fullname')
                    };

                    self.searchForm.numberSuggests.push(runner);
                    self.searchForm.showNumberDropdown = true;
                }
            });
        },

        /**
         * Remove number dropdown
         * @param event
         */
        removeNumberDropdownSuggest: function (event) {
            const self = this;
            const input = event.target;
            window.setTimeout(function () {
                $(input).val('');
                self.searchForm.numberSuggests = [];
                self.searchForm.showNumberDropdown = false;
            }, 50);
        },

        /**
         * Show number dropdown
         * @param event
         */
        showNameDropdownSuggest: function (event) {
            const self = this;
            const input = event.target;

            if ($(input).val() === '') {
                self.searchForm.nameSuggests = [];
                self.searchForm.showNameDropdown = false;
                return;
            }
            // Clean number input
            $('#searchNumber').val('');
            self.searchForm.numberSuggests = [];
            self.searchForm.showNumberDropdown = false;

            const rows = $('#startlistTable tbody tr');

            const regex = new RegExp($(input).val() + '(.*)', 'i');

            self.searchForm.nameSuggests = [];

            rows.each(function () {
                if (regex.test($(this).attr('data-fullname'))) {

                    const runner = {
                        index: $(this).attr('data-index'),
                        number: $(this).attr('data-number'),
                        fullname: $(this).attr('data-fullname')
                    };

                    self.searchForm.nameSuggests.push(runner);
                    self.searchForm.showNameDropdown = true;
                }
            });
        },

        /**
         * Remove name dropdown
         * @param event
         */
        removeNameDropdownSuggest: function (event) {
            const self = this;
            const input = event.target;
            window.setTimeout(function () {
                $(input).val('');
                self.searchForm.nameSuggests = [];
                self.searchForm.showNameDropdown = false;
            }, 50);
        },

        /**
         * Apply filter
         * @param event
         */
        applyFilter: function (event) {
            const select = event.target;

            // Filter option
            const $filterCat = $(select).val();
            const rows = $('.startlist-table tbody tr');
            rows.removeClass('d-none');

            if ($filterCat === '0') return;

            rows.each(function () {
                if ($(this).attr('data-category') !== $filterCat) {
                    $(this).addClass('d-none');
                }
            });
        },

        /**
         * Get formated time
         * @param d
         * @returns {string}
         */
        getFormatedTime: function (d) {
            const hours = d.getHours() < 10 ? '0' + d.getHours() : d.getHours();
            const minutes = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
            const seconds = d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds();
            return hours + ":" + minutes + ":" + seconds;
        }
    }
});
