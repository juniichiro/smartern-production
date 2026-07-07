import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

/**
 * MFA Verification Timer Component
 */
Alpine.data('mfaTimer', (initialTime) => ({
    timeLeft: initialTime,
    init() {
        setInterval(() => {
            if (this.timeLeft > -2) {
                this.timeLeft--;
            } else {
                window.location.reload();
            }
        }, 1000);
    }
}));

/**
 * Dark Mode Management Component
 */
Alpine.data('darkModeManager', () => ({
    darkMode: localStorage.getItem('darkMode') === 'true' || 
             (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    init() {
        this.$watch('darkMode', val => { 
            localStorage.setItem('darkMode', val); 
            this.apply();
        });
        this.apply();
    },
    apply() {
        if (this.darkMode) document.documentElement.classList.add('dark'); 
        else document.documentElement.classList.remove('dark');
    }
}));

/**
 * Simple Notification/Alert Fade component
 */
Alpine.data('uiNotification', (timeout = 2000) => ({
    show: true,
    init() {
        if (timeout > 0) {
            setTimeout(() => this.show = false, timeout);
        }
    }
}));

/**
 * Toast Notification Manager
 * Handles global and localized (contextual) toast notifications
 */
Alpine.data('toastManager', (context = null) => ({
    toasts: [],
    queue: [],
    init() {
        window.addEventListener('toast', (e) => {
            const { message, type, target } = e.detail;
            
            // If this manager has a context, only accept toasts for this target
            if (context && target !== context) return;
            
            // If this is a global manager (no context), only accept toasts with no target
            if (!context && target) return;

            this.enqueue(message, type);
        });

        // Initialize global helper if this is the global manager
        if (!context) {
            window.showToast = (message, type = 'success', target = null) => {
                window.dispatchEvent(new CustomEvent('toast', { 
                    detail: { message, type, target } 
                }));
            };
        }
    },
    enqueue(message, type = 'success') {
        const isDuplicate = this.toasts.some(t => t.message === message && t.type === type) || 
                            this.queue.some(q => q.message === message && q.type === type);
        if (isDuplicate) return;

        const id = Date.now() + Math.random();
        this.queue.push({ id, message, type, show: false });
        this.processQueue();
    },
    processQueue() {
        if (this.toasts.length > 0) return;
        if (this.queue.length === 0) return;

        const nextToast = this.queue.shift();
        this.toasts.push(nextToast);

        this.$nextTick(() => {
            const toast = this.toasts.find(t => t.id === nextToast.id);
            if (toast) toast.show = true;
        });

        setTimeout(() => this.remove(nextToast.id), 5000);
    },
    remove(id) {
        const toast = this.toasts.find(t => t.id === id);
        if (toast) {
            toast.show = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
                this.processQueue();
            }, 300);
        }
    }
}));

/**
 * Basic Dropdown component
 */
Alpine.data('uiDropdown', () => ({
    open: false,
    toggle() { this.open = !this.open },
    close() { this.open = false }
}));

/**
 * Basic Modal component
 */
Alpine.data('uiModal', (initialState = false) => ({
    show: initialState,
    open() { this.show = true },
    close() { this.show = false }
}));

/**
 * Tab Manager component
 */
Alpine.data('tabManager', (initialTab = '', hashAnchor = false) => ({
    activeTab: initialTab,
    init() {
        if (hashAnchor && window.location.hash) {
            this.activeTab = window.location.hash.replace('#', '');
        }
    },
    setTab(tab) {
        this.activeTab = tab;
        if (hashAnchor) window.location.hash = tab;
    }
}));

/**
 * Admin Accounts Manager
 */
Alpine.data('accountsManager', (hasHrHead, hasErrors = false, initialRole = '', isUpdate = false, initialData = null) => ({
    loading: false,
    showAddModal: hasErrors && !isUpdate,
    showDeleteModal: false,
    showEditModal: hasErrors && isUpdate,
    showImportModal: false,
    userToDelete: null,
    hasHrHead: hasHrHead,
    selectedRole: initialRole,
    gender: '',       
    photoPreview: null,
    fileName: 'Click or drag CSV here',
    deletingIds: [],
    editData: initialData || { 
        id: '', email: '', role: '', first_name: '', middle_name: '', last_name: '', 
        phone_number: '', office_id: '', school: '', gender: '', 
        gender_other_specify: '', is_hr_head: false, profile_picture: '',
        initials: ''
    },
    init() {},
    confirmDelete(publicId) {
        this.userToDelete = publicId;
        this.showDeleteModal = true;
    },
    triggerDelete() {
        this.deletingIds.push(this.userToDelete);
        this.showDeleteModal = false;
        // The form will submit naturally, but we've now optimistically hidden the user
        setTimeout(() => {
            document.getElementById('delete-confirm-form').submit();
        }, 100);
    },
    handleFileChange(event) {
        const file = event.target.files[0];
        this.fileName = file ? file.name : 'Click or drag CSV here';
    }
}));

/**
 * Admin Configuration Manager
 */
Alpine.data('configurationManager', () => ({
    loading: false,
    showRollbackModal: false,
    rollbackId: null,
    showDeleteModal: false,
    deleteId: null,
    showUploadModal: false,
    isRestoring: false,
    isUploading: false,
    init() {},
    confirmRollback(id) {
        this.rollbackId = id;
        this.showRollbackModal = true;
    },
    triggerRestore() {
        this.showRollbackModal = false;
        this.isRestoring = true;
        document.getElementById('rollback-form-' + this.rollbackId).submit();
    },
    confirmDelete(id) {
        this.deleteId = id;
        this.showDeleteModal = true;
    },
    triggerDelete() {
        this.showDeleteModal = false;
        document.getElementById('delete-form-' + this.deleteId).submit();
    }
}));

/**
 * Head Supervisor Office Matching Manager
 */
Alpine.data('officeMatchingManager', () => ({
    showUploadModal: false,
    showClearModal: false,
    showFinalizeModal: false,
    selectedApplicants: [],
    selectedOffice: '',
    expanded: null, 
    isDropdownOpen: false,
    batchOfficeId: '',
    batchOfficeName: 'Select Assignment',
    
    isSelected(name) {
        return this.selectedApplicants.includes(name);
    },
    toggleApplicant(name) {
        const index = this.selectedApplicants.indexOf(name);
        if (index > -1) {
            this.selectedApplicants.splice(index, 1);
        } else {
            this.selectedApplicants.push(name);
        }
    },
    selectAll(names) {
        if (this.selectedApplicants.length === names.length) {
            this.selectedApplicants = [];
        } else {
            this.selectedApplicants = [...names];
        }
    }
}));

/**
 * Geofence Overview Map Manager
 */
Alpine.data('geofenceManager', (officesData) => ({
    loading: false,
    geofenceOfficeOpen: false,
    geofenceData: {latitude: '', longitude: '', radius_meters: 100, address: 'Fetching address...'},
    searchQuery: '',
    isSearching: false,
    map: null,
    marker: null,
    circle: null,
    offices: officesData,
    selectedOfficeId: '',

    init() {
        // Run immediately if loading is already false, otherwise watch for changes
        const tryInit = () => {
            if (typeof L !== 'undefined') {
                this.initMainMap();
            } else {
                setTimeout(tryInit, 50);
            }
        };
        
        if (!this.loading) {
            this.$nextTick(() => {
                tryInit();
            });
        }

        this.$watch('loading', (value) => {
            if (!value) {
                this.$nextTick(() => {
                    tryInit();
                });
            }
        });
    },

    changeTargetOffice() {
        if (this.selectedOfficeId) {
            const office = this.offices.find(o => o.id == this.selectedOfficeId);
            if (office) {
                this.geofenceData.latitude = office.latitude || '14.5995';
                this.geofenceData.longitude = office.longitude || '120.9842';
                this.geofenceData.radius_meters = office.radius_meters || 100;
                this.updateFromInputs();
            }
        } else {
            if (this.offices.length > 0) {
                this.geofenceData.latitude = this.offices[0].latitude || '14.5995';
                this.geofenceData.longitude = this.offices[0].longitude || '120.9842';
                this.geofenceData.radius_meters = this.offices[0].radius_meters || 100;
                this.updateFromInputs();
            }
        }
    },

    async fetchAddress(lat, lng) {
        this.geofenceData.address = 'Fetching address...';
        try {
            const response = await fetch(`https:// nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                headers: { 'User-Agent': 'SmarTern-IMS/1.0' }
            });
            const data = await response.json();
            this.geofenceData.address = data.display_name || 'Location name not found';
        } catch (error) {
            this.geofenceData.address = 'Error fetching address';
            console.error('Geocoding error:', error);
        }
    },
    
    initMainMap() {
        this.$nextTick(() => {
            const map = L.map('main-map').setView([14.5995, 120.9842], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors'
            }).addTo(map);

            this.offices.forEach(office => {
                if (office.latitude && office.longitude) {
                    const marker = L.marker([office.latitude, office.longitude]).addTo(map);
                    marker.bindPopup(`<b>${office.name}</b><br>Radius: ${office.radius_meters}m`);
                    
                    L.circle([office.latitude, office.longitude], {
                        radius: office.radius_meters,
                        color: '#0B5694',
                        fillColor: '#0B5694',
                        fillOpacity: 0.15
                    }).addTo(map);
                }
            });
            
            // If there are offices, fit bounds
            const activeOffices = this.offices.filter(o => o.latitude && o.longitude);
            if (activeOffices.length > 0) {
                const group = new L.featureGroup(activeOffices.map(o => L.marker([o.latitude, o.longitude])));
                map.fitBounds(group.getBounds().pad(0.1));

                // Fetch address for the first office (division-wide)
                this.fetchAddress(activeOffices[0].latitude, activeOffices[0].longitude);
            }

            // Force recalculation of dimensions after transition
            setTimeout(() => {
                map.invalidateSize({ pan: true });
            }, 600);
        });
    },

    initEditMap() {
        this.$nextTick(() => {
            const lat = parseFloat(this.geofenceData.latitude) || 14.5995;
            const lng = parseFloat(this.geofenceData.longitude) || 120.9842;
            const radius = parseInt(this.geofenceData.radius_meters) || 100;

            this.fetchAddress(lat, lng);

            if (!this.map) {
                this.map = L.map('edit-map').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors'
                }).addTo(this.map);

                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this.circle = L.circle([lat, lng], {
                    radius: radius,
                    color: '#0B5694',
                    fillColor: '#0B5694',
                    fillOpacity: 0.2
                }).addTo(this.map);
                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.geofenceData.latitude = position.lat.toFixed(8);
                    this.geofenceData.longitude = position.lng.toFixed(8);
                    this.circle.setLatLng(position);
                    this.fetchAddress(position.lat, position.lng);
                });

                this.map.on('click', (e) => {
                    const position = e.latlng;
                    this.geofenceData.latitude = position.lat.toFixed(8);
                    this.geofenceData.longitude = position.lng.toFixed(8);
                    this.marker.setLatLng(position);
                    this.circle.setLatLng(position);
                    this.fetchAddress(position.lat, position.lng);
                });

                // Force recalculation for edit map modal
                setTimeout(() => {
                    this.map.invalidateSize({ pan: true });
                }, 500);
            } else {
                this.map.setView([lat, lng], 16);
                if (this.marker) this.marker.setLatLng([lat, lng]);
                if (this.circle) {
                    this.circle.setLatLng([lat, lng]);
                    this.circle.setRadius(radius);
                }
                setTimeout(() => {
                    this.map.invalidateSize({ pan: true });
                }, 200);
            }
        });
    },

    async searchLocation(query) {
        if (!query || query.length < 3) return;
        this.isSearching = true;
        try {
            const response = await fetch(`https:// nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=one`);
            const data = await response.json();
            if (data.length > 0) {
                const result = data[0];
                this.geofenceData.latitude = parseFloat(result.lat).toFixed(8);
                this.geofenceData.longitude = parseFloat(result.lon).toFixed(8);
                this.updateFromInputs();
            }
        } catch (error) {
            console.error('Search failed:', error);
        } finally {
            this.isSearching = false;
        }
    },

    updateFromInputs() {
        const lat = parseFloat(this.geofenceData.latitude);
        const lng = parseFloat(this.geofenceData.longitude);
        const radius = parseInt(this.geofenceData.radius_meters);
        
        if (!isNaN(lat) && !isNaN(lng)) {
            if (this.marker) this.marker.setLatLng([lat, lng]);
            if (this.circle) this.circle.setLatLng([lat, lng]);
            if (this.map) this.map.panTo([lat, lng]);
        }
        if (!isNaN(radius) && this.circle) {
            this.circle.setRadius(radius);
        }
    }
}));

/**
 * Onboarding Form Manager
 */
Alpine.data('onboardingFormManager', (initialTech, initialSoft, initialGender) => ({
    techSkills: initialTech,
    softSkills: initialSoft,
    gender: initialGender,
    addTech() { this.techSkills.push({ name: '', score: '' }) },
    removeTech(index) { this.techSkills.splice(index, 1) },
    addSoft() { this.softSkills.push({ name: '', score: '' }) },
    removeSoft(index) { this.softSkills.splice(index, 1) }
}));

/**
 * Supervisor Task Management
 */
Alpine.data('supervisorTaskManager', () => ({
    showAddModal: false,
    showDeleteModal: false,
    showAssignModal: false,
    showEditModal: false,
    taskToDelete: null,
    selectedInterns: [],
    editData: { id: '', title: '', description: '', due_date: '', is_file_required: true },

    selectAllInterns(ids) {
        this.selectedInterns = this.selectedInterns.length === ids.length ? [] : [...ids];
    },

    openEditModal(id, title, desc, date, isFileRequired) {
        this.editData = { id, title, description: desc, due_date: date, is_file_required: isFileRequired };
        this.showEditModal = true;
    },

    confirmDelete(id) {
        this.taskToDelete = id;
        this.showDeleteModal = true;
    },
}));

/**
 * Supervisor Dashboard Manager
 */
Alpine.data('supervisorDashboardManager', () => ({
    loading: false,
    init() {}
}));

/**
 * Head Supervisor Dashboard Manager
 */
Alpine.data('headSupervisorDashboardManager', (initialTab = 'division') => ({
    loading: false,
    activeTab: initialTab,
    init() {
        if (window.location.hash) {
            this.activeTab = window.location.hash.replace('#', '');
        }
        },
    setTab(tab) {
        this.activeTab = tab;
        window.location.hash = tab;
    }
}));

/**
 * Intern Directory Manager
 */
Alpine.data('internDirectoryManager', () => ({
    loading: false,
    searchQuery: '',
    statusFilter: 'all',
    deptFilter: 'all',

    init() {},

    matches(name, email, office, status) {
        const query = this.searchQuery.toLowerCase();
        const matchesSearch = !query || 
                             name.toLowerCase().includes(query) || 
                             email.toLowerCase().includes(query);
        
        const matchesStatus = this.statusFilter === 'all' || 
                             status.toLowerCase() === this.statusFilter.toLowerCase();
        
        const matchesDept = this.deptFilter === 'all' || 
                           office === this.deptFilter;

        return matchesSearch && matchesStatus && matchesDept;
    }
}));

/**
 * Intern Profile Manager
 */
Alpine.data('internProfileManager', () => ({
    loading: false,
    init() {}
}));

/**
 * Supervisor Announcements Manager
 */
Alpine.data('announcementsManager', () => ({
    loading: false,
    showModal: false, 
    isEdit: false,
    editId: null,
    fileName: 'Attach supporting media',
    title: '',
    scope: 'global',
    contentLength: 0,
    maxTitle: 255,
    maxContent: 10000,
    previewMode: false,
    previewContent: '',
    showDeleteModal: false,
    showViewModal: false,
    viewAnnouncement: null,
    deleteRoute: '',
    deleteTitle: '',
    
    init() {},

    syncContent() {
        const code = $('#announcement-editor').summernote('code');
        $('#announcement-editor').val(code);
        this.contentLength = code.length;
    },

    handleFileChange(event) {
        const file = event.target.files[0];
        this.fileName = file ? file.name : 'Attach supporting media';
    },

    openCreateModal() {
        this.isEdit = false;
        this.editId = null;
        this.title = '';
        this.scope = 'global';
        this.contentLength = 0;
        this.fileName = 'Attach supporting media';
        this.previewMode = false;
        this.showModal = true;
        // Explicitly clear editor content on next tick
        setTimeout(() => {
            if ($('#announcement-editor').data('summernote')) {
                $('#announcement-editor').summernote('code', '');
                $('#announcement-editor').val('');
            }
        }, 150);
    },

    openEditModal(ann, routeKey) {
        this.isEdit = true;
        this.editId = routeKey;
        this.title = ann.title;
        this.scope = ann.office_id ? 'office' : 'global';
        this.contentLength = ann.content ? ann.content.length : 0;
        this.fileName = ann.attachment ? ann.attachment.split('/').pop() : 'Attach supporting media';
        this.previewMode = false;
        this.showModal = true;
        setTimeout(() => {
            $('#announcement-editor').summernote('code', ann.content || '');
            // Also sync to textarea immediately
            $('#announcement-editor').val(ann.content || '');
        }, 200);
    },

    enterPreview() {
        this.syncContent();
        this.previewContent = $('#announcement-editor').val();
        this.previewMode = true;
    },

    openDeleteModal(title, route) {
        this.deleteTitle = title;
        this.deleteRoute = route;
        this.showDeleteModal = true;
    },

    openViewModal(ann) {
        this.viewAnnouncement = ann;
        this.showViewModal = true;
    }
}));

/**
 * Custom Datepicker Component Logic
 */
Alpine.data('customDatePicker', (config) => ({
    show: false,
    value: config.value || '',
    displayValue: '',
    type: config.type || 'date',
    month: '',
    year: '',
    noOfDays: [],
    blankDays: [],
    hours: 12,
    minutes: '00',
    ampm: 'AM',
    isValid: false,
    position: 'bottom',
    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

    init() {
        this.syncFromValue();
        this.getNoOfDays();
        this.formatDisplay();

        this.$watch('show', (val) => {
            if (val) {
                this.calculatePosition();
            } else {
                this.commitChanges();
            }
        });

        // Crucial for modal "Edit" support: sync when the bound value changes externally
        this.$watch('value', (val) => {
            if (val && !this.show) {
                this.syncFromValue();
                this.formatDisplay();
            }
        });
    },

    syncFromValue() {
        let dateObj = this.value ? new Date(this.value.replace(' ', 'T')) : new Date();
        if (isNaN(dateObj.getTime())) {
            // Fallback for simple date strings from DB or manual input
            let [datePart] = (this.value || '').split(' ');
            dateObj = datePart ? new Date(datePart) : new Date();
        }
        
        if (!isNaN(dateObj.getTime())) {
            this.month = dateObj.getMonth();
            this.year = dateObj.getFullYear();
            
            if (this.type === 'datetime-local') {
                let h = dateObj.getHours();
                this.ampm = h >= 12 ? 'PM' : 'AM';
                this.hours = h % 12 || 12;
                this.minutes = dateObj.getMinutes().toString().padStart(2, '0');
            }
        }
    },

    calculatePosition() {
        this.$nextTick(() => {
            const rect = this.$el.getBoundingClientRect();
            const dropdownHeight = 450;
            const spaceBelow = window.innerHeight - rect.bottom;
            this.position = (spaceBelow < dropdownHeight && rect.top > dropdownHeight) ? 'top' : 'bottom';
        });
    },

    getNoOfDays() {
        let daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
        let dayOfWeek = new Date(this.year, this.month).getDay();
        let blankdaysArray = [];
        for (var i = 1; i <= dayOfWeek; i++) {
            blankdaysArray.push(i);
        }
        let daysArray = [];
        for (var i = 1; i <= daysInMonth; i++) {
            daysArray.push(i);
        }
        this.blankDays = blankdaysArray;
        this.noOfDays = daysArray;
    },

    selectDate(date) {
        let selectedDate = new Date(this.year, this.month, date);
        this.value = this.formatDate(selectedDate);
        this.updateValue();
        if (this.type === 'date') this.show = false;
    },

    updateValue() {
        if (!this.value) return;
        
        let [datePart] = this.value.split(' ');
        if (this.type === 'datetime-local') {
            let h = parseInt(this.hours);
            if (this.ampm === 'PM' && h < 12) h += 12;
            if (this.ampm === 'AM' && h === 12) h = 0;
            let m = this.minutes.toString().padStart(2, '0');
            this.value = `${datePart} ${h.toString().padStart(2, '0')}:${m}:00`;
        } else {
            this.value = datePart;
        }
        this.formatDisplay();
    },

    handleTimeScroll(event, target) {
        event.preventDefault();
        let delta = event.deltaY < 0 ? 1 : -1;
        if (target === 'hours') {
            let newVal = parseInt(this.hours) + delta;
            if (newVal > 12) newVal = 1;
            if (newVal < 1) newVal = 12;
            this.hours = newVal;
        } else {
            let newVal = parseInt(this.minutes) + delta;
            if (newVal > 59) newVal = 0;
            if (newVal < 0) newVal = 59;
            this.minutes = newVal.toString().padStart(2, '0');
        }
        this.updateValue();
    },

    formatDate(date) {
        let d = new Date(date);
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        let year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    },

    formatDisplay() {
        if (!this.value) {
            this.displayValue = '';
            this.isValid = false;
            return;
        }
        let d = new Date(this.value.replace(' ', 'T'));
        if (isNaN(d.getTime())) {
            let [datePart] = this.value.split(' ');
            d = new Date(datePart);
        }
        if (isNaN(d.getTime())) {
            this.isValid = false;
            return;
        }

        let options = { year: 'numeric', month: 'short', day: 'numeric' };
        if (this.type === 'datetime-local') {
            options.hour = '2-digit';
            options.minute = '2-digit';
            options.hour12 = true;
        }
        this.displayValue = d.toLocaleString('en-US', options);
        this.isValid = true;
    },

    commitChanges() {
        this.updateValue();
    },

    isSelected(date) {
        if (!this.value) return false;
        let [datePart] = this.value.split(' ');
        let d = new Date(datePart + 'T00:00:00');
        return d.getDate() === date && d.getMonth() === this.month && d.getFullYear() === this.year;
    },

    isToday(date) {
        let today = new Date();
        return today.getDate() === date && today.getMonth() === this.month && today.getFullYear() === this.year;
    },

    prevMonth() {
        if (this.month === 0) {
            this.month = 11;
            this.year--;
        } else {
            this.month--;
        }
        this.getNoOfDays();
    },

    nextMonth() {
        if (this.month === 11) {
            this.month = 0;
            this.year++;
        } else {
            this.month++;
        }
        this.getNoOfDays();
    },

    setToday() {
        let today = new Date();
        this.month = today.getMonth();
        this.year = today.getFullYear();
        this.getNoOfDays();
        this.selectDate(today.getDate());
    },

    clearDate() {
        this.value = '';
        this.displayValue = '';
        this.isValid = false;
        this.show = false;
    }
}));

/**
 * Sidebar Management Component
 */
Alpine.data('sidebarManager', () => ({
    sidebarOpen: false,
    desktopCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    init() {
        this.$watch('desktopCollapsed', value => {
            localStorage.setItem('sidebarCollapsed', value);
            document.body.classList.toggle('sidebar-collapsed', value);
        });
        document.body.classList.toggle('sidebar-collapsed', this.desktopCollapsed);
    }
}));

Alpine.data('notificationManager', (userId, initialNotifications) => ({
    notifOpen: false,
    notifications: initialNotifications,
    unreadCount: initialNotifications.filter(n => !n.is_read).length,
    allRead: false,
    desktopNotifCollapsed: localStorage.getItem('notifCollapsed') === 'true',
    init() {
        this.$watch('unreadCount', value => {
            window.dispatchEvent(new CustomEvent('update-unread', { detail: { count: value } }));
        });
        this.$watch('desktopNotifCollapsed', value => {
            localStorage.setItem('notifCollapsed', value);
            document.body.classList.toggle('notif-collapsed', value);
            if (!value) this.clearNotifications();
        });
        this.$watch('notifOpen', value => {
            if (value) this.clearNotifications();
        });
        document.body.classList.toggle('notif-collapsed', this.desktopNotifCollapsed);

        // Dispatch initial unread count so mobile header badge renders correctly
        window.dispatchEvent(new CustomEvent('update-unread', { detail: { count: this.unreadCount } }));

        // Real-Time Listener
        if (window.Echo) {
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.notification.sent', (e) => {
                    this.unreadCount++;
                    this.allRead = false;
                    
                    // Format the incoming notification
                    const newNotif = {
                        id: e.notification.id,
                        category: e.notification.category,
                        message: e.notification.message,
                        is_read: false,
                        target_url: e.notification.target_url,
                        time_ago: '1s',
                        date_group: 'Today'
                    };
                    
                    // Unshift to the top of our array
                    this.notifications.unshift(newNotif);
                    
                    if (window.showToast) {
                        window.showToast(e.notification.message, 'info');
                    } else {
                        console.log('New notification:', e.notification);
                    }
                });
        }
    },
    get groupedNotifications() {
        const groups = {};
        this.notifications.forEach(notif => {
            const group = notif.date_group;
            if (!groups[group]) groups[group] = [];
            groups[group].push(notif);
        });
        return groups;
    },
    clearNotifications() {
        if (this.unreadCount > 0) {
            this.unreadCount = 0;
            this.allRead = true;
            
            // Mark all local notifications as read
            this.notifications.forEach(notif => {
                notif.is_read = true;
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .catch(error => console.error('Error clearing notifications:', error));
        }
    }
}));

/**
 * Supervisor Intern Tasks Filter
 */
Alpine.data('supervisorInternTasks', () => ({
    loading: false,
    searchQuery: '',
    statusFilter: 'all',
    init() {}
}));

/**
 * Supervisor Task Preview
 */
Alpine.data('supervisorTaskPreview', () => ({
    loading: false,
    showPreview: false,
    fileUrl: '',
    fileType: '',
    
    init() {}
}));

/**
 * Task Monitoring Manager
 */
Alpine.data('taskMonitoringManager', (initialHash) => {
    let show = false;
    let selected = 'all';
    if (initialHash) {
        if (initialHash === '#assignment-form') {
            show = true;
        } else if (initialHash.startsWith('#assign-')) {
            show = true;
            selected = initialHash.replace('#assign-', '');
        }
    }
    return {
        loading: false,
        showDrawer: show,
        selectedInternId: selected,
        
        init() {}
    };
});

/**
 * Supervisor Attendance Manager
 */
Alpine.data('supervisorAttendanceManager', () => ({
    loading: false,
    showLogsDrawer: false,
    selectedInternId: null,
    activeTab: 'all',
    drawerTab: 'all',
    activeView: 'timeline',
    searchQuery: '',
    
    init() {}
}));

/**
 * Head Supervisor Reports Manager
 */
Alpine.data('headSupervisorReportsManager', () => ({
    loading: false,
    init() {}
}));

/**
 * Head Supervisor Certificates Manager
 */
Alpine.data('headSupervisorCertificatesManager', () => ({
    loading: false,
    init() {}
}));

/**
 * Supervisor Certificates Manager
 */
Alpine.data('supervisorCertificatesManager', () => ({
    loading: false,
    showFeedbackModal: false,
    selectedInternName: '',
    endorseUrl: '',
    init() {},
    openFeedbackModal(name, url) {
        this.selectedInternName = name;
        this.endorseUrl = url;
        this.showFeedbackModal = true;
    },
    closeFeedbackModal() {
        this.showFeedbackModal = false;
        this.selectedInternName = '';
        this.endorseUrl = '';
    }
}));

/**
 * Supervisor Certificate Preview Manager
 */
Alpine.data('supervisorCertificatePreviewManager', () => ({
    loading: false,
    showFeedbackModal: false,
    init() {}
}));

/**
 * Head Supervisor Certificate Preview Manager
 */
Alpine.data('headSupervisorCertificatePreviewManager', () => ({
    loading: false,
    init() {}
}));

/**
 * Intern Task Submission Manager
 */
Alpine.data('internTaskSubmission', () => ({
    fileName: '',
    submitting: false,
    handleFileChange(event) {
        const file = event.target.files[0];
        this.fileName = file ? file.name : '';
    },
    submitForm(event) {
        this.submitting = true;
        // The form will submit naturally, but we've now locked the UI
        // and showed a loading state optimistically
    }
}));

/**
 * Head Supervisor Onboarding Manager
 */
Alpine.data('headSupervisorOnboardingManager', () => ({
    loading: false,
    resumeUrl: '', 
    showResumePreview: false,
    init() {},
    openResume(url) {
        this.resumeUrl = url;
        this.showResumePreview = true;
    }
}));

/**
 * Head Supervisor Office Manager
 */
Alpine.data('officeManager', () => ({
    showAddModal: false,
    showDeleteModal: false,
    officeToDelete: null
}));

/**
 * Change Password Validation Component
 */
Alpine.data('changePasswordManager', () => ({
    password: '',
    password_confirmation: '',
    rules: {
        length: false,
        lower: false,
        upper: false,
        number: false,
        special: false
    },
    validate() {
        this.rules.length = this.password.length >= 12;
        this.rules.lower = /[a-z]/.test(this.password);
        this.rules.upper = /[A-Z]/.test(this.password);
        this.rules.number = /[0-9]/.test(this.password);
        this.rules.special = /[^A-Za-z0-9]/.test(this.password);
    },
    get allMet() {
        return Object.values(this.rules).every(Boolean) && 
               this.password === this.password_confirmation && 
               this.password !== '';
    }
}));

/**
 * Head Supervisor Intern Profile Manager
 */
Alpine.data('headSupervisorInternProfile', () => ({
    loading: false,
    activeTab: 'tasks',
    modalOpen: false,
    selectedJournal: {title: '', date: '', content: ''},
    attendancePage: 1,
    attendancePerPage: 10,
    init() {}
}));

/**
 * Head Supervisor Office Management
 */
Alpine.data('headSupervisorOfficeManager', () => ({
    loading: false,
    addOfficeOpen: false, 
    editOfficeOpen: false, 
    deleteOfficeOpen: false,
    geofenceOfficeOpen: false,
    editOffice: {id: '', name: ''},
    deleteOffice: {id: '', name: ''},
    geofenceOffice: {latitude: '', longitude: '', radius_meters: 100, address: 'Fetching address...'},
    map: null,
    marker: null,
    circle: null,
    
    init() {},
    
    async fetchAddress(lat, lng) {
        this.geofenceOffice.address = 'Fetching address...';
        try {
            const response = await fetch(`https:// nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                headers: { 'User-Agent': 'SmarTern-IMS/1.0' }
            });
            const data = await response.json();
            this.geofenceOffice.address = data.display_name || 'Location name not found';
        } catch (error) {
            this.geofenceOffice.address = 'Error fetching address';
            console.error('Geocoding error:', error);
        }
    },
    
    initMap() {
        this.$nextTick(() => {
            const lat = parseFloat(this.geofenceOffice.latitude) || 14.5995;
            const lng = parseFloat(this.geofenceOffice.longitude) || 120.9842;
            const radius = parseInt(this.geofenceOffice.radius_meters) || 100;
 
            this.fetchAddress(lat, lng);
 
            if (!this.map) {
                this.map = L.map('map').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors'
                }).addTo(this.map);
 
                this.marker = L.marker([lat, lng], { draggable: false }).addTo(this.map);
                this.circle = L.circle([lat, lng], {
                    radius: radius,
                    color: '#0B5694',
                    fillColor: '#0B5694',
                    fillOpacity: 0.2
                }).addTo(this.map);
            } else {
                this.map.setView([lat, lng], 16);
                this.marker.setLatLng([lat, lng]);
                this.circle.setLatLng([lat, lng]);
                this.circle.setRadius(radius);
            }
            setTimeout(() => { this.map.invalidateSize(); }, 100);
        });
    }
}));

/**
 * Form Loading State
 */
Alpine.data('loadingState', () => ({
    loading: false
}));

/**
 * Focused Modal Component with focus trapping
 */
Alpine.data('uiModalFocused', (showInitial = false) => ({
    show: showInitial,
    focusables() {
        let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';
        return [...this.$el.querySelectorAll(selector)].filter(el => !el.hasAttribute('disabled'));
    },
    firstFocusable() { return this.focusables()[0] },
    lastFocusable() { return this.focusables().slice(-1)[0] },
    nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
    prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
    nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
    prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1 },
    init() {
        this.$watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
    }
}));

/**
 * Global Notification Handler
 */
window.handleNotificationClick = function(id, csrfToken) {
    fetch(`/notifications/${id}/resolve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        }
        })
        .catch(error => console.error('Error resolving notification:', error));
        };

        /**
        * Head Supervisor Analytics Manager
 */
Alpine.data('analyticsManager', (months, applicantsTrends, completionTrends, completionRateTrends, schoolLabels, schoolTotals, initialTab = 'analytics') => ({
    loading: false,
    activeTab: initialTab,
    trendFilter: 'rate',
    schoolFilter: '5',
    showFilters: false,
    months: months,
    applicantsTrends: applicantsTrends,
    completionTrends: completionTrends,
    completionRateTrends: completionRateTrends,
    schoolLabels: schoolLabels,
    schoolTotals: schoolTotals,
    init() {
        this.waitForChart();
        },
    waitForChart(retries = 0) {
        if (window.Chart) {
            this.initCharts();
        } else if (retries < 10) {
            setTimeout(() => this.waitForChart(retries + 1), 200);
        }
    },
    switchTab(tab) {
        this.activeTab = tab;
        if (tab === 'analytics') {
            this.$nextTick(() => {
                this.initCharts();
            });
        }
    },
    setTrendFilter(filter) {
        this.trendFilter = filter;
        this.initCharts();
    },
    setSchoolFilter(filter) {
        this.schoolFilter = filter;
        this.initCharts();
    },
    initCharts() {
        if (!document.getElementById('completionChart')) return;

        // Destroy existing charts if they exist to allow re-initialization on theme switch
        if (window.completionChartInstance) window.completionChartInstance.destroy();
        if (window.schoolChartInstance) window.schoolChartInstance.destroy();

        const isDark = document.documentElement.classList.contains('dark');
        console.log("Chart Theme Sync - Is Dark:", isDark);
        
        // High-Contrast colors for maximum readability
        const textColor = isDark ? '#ffffff' : '#1f2937'; 
        const secondaryTextColor = isDark ? '#d1d5db' : '#4b5563';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

        // Set global defaults for this render cycle
        Chart.defaults.color = textColor;
        Chart.defaults.font.family = "'Outfit', sans-serif";

        const datasets = [];

        if (this.trendFilter === 'applicants') {
            datasets.push({
                label: 'Applicants',
                data: this.applicantsTrends,
                backgroundColor: '#8b5cf6',
                borderRadius: 4,
                barThickness: 12
            });
        }

        if (this.trendFilter === 'completions') {
            datasets.push({
                label: 'Completions',
                data: this.completionTrends,
                backgroundColor: '#0B5694',
                borderRadius: 4,
                barThickness: 12
            });
        }

        if (this.trendFilter === 'rate') {
            datasets.push({
                label: 'Completion Rate (%)',
                data: this.completionRateTrends,
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                borderWidth: 2.5,
                type: 'line',
                yAxisID: 'y',
                pointBackgroundColor: '#10b981',
                pointHoverRadius: 6,
                tension: 0.3
            });
        }

        const completionCtx = document.getElementById('completionChart').getContext('2d');
        window.completionChartInstance = new Chart(completionCtx, {
            type: 'bar',
            data: {
                labels: this.months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'top',
                        align: 'end',
                        labels: { 
                            color: textColor,
                            usePointStyle: true, 
                            boxWidth: 8, 
                            font: { weight: '800', size: 11 } 
                        }
                    } 
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: gridColor }, 
                        border: { display: false },
                        ticks: { 
                            color: secondaryTextColor, 
                            font: { weight: '700', size: 11 },
                            callback: (value) => {
                                return this.trendFilter === 'rate' ? value + '%' : value;
                            }
                        }
                    },
                    x: { 
                        grid: { display: false }, 
                        border: { display: false },
                        ticks: { color: secondaryTextColor, font: { weight: '700', size: 11 } }
                    }
                }
            }
        });

        // Group school data if filtered
        let processedSchoolLabels = [...this.schoolLabels];
        let processedSchoolTotals = [...this.schoolTotals];

        const schoolLimit = parseInt(this.schoolFilter);
        if (!isNaN(schoolLimit) && processedSchoolLabels.length > schoolLimit) {
            const topLabels = processedSchoolLabels.slice(0, schoolLimit);
            const topTotals = processedSchoolTotals.slice(0, schoolLimit);
            
            const othersTotal = processedSchoolTotals.slice(schoolLimit).reduce((sum, val) => sum + val, 0);
            
            if (othersTotal > 0) {
                topLabels.push('Others');
                topTotals.push(othersTotal);
            }
            
            processedSchoolLabels = topLabels;
            processedSchoolTotals = topTotals;
        }

        const schoolCtx = document.getElementById('schoolChart').getContext('2d');
        const colors = ['#0B5694', '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#f97316', '#06b6d4', '#84cc16'];
        window.schoolChartInstance = new Chart(schoolCtx, {
            type: 'doughnut',
            data: {
                labels: processedSchoolLabels,
                datasets: [{
                    data: processedSchoolTotals,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: isDark ? '#111827' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { 
                            color: textColor,
                            usePointStyle: true, 
                            padding: 25, 
                            font: { weight: '800', size: 11 },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: (label.length > 25 ? label.substring(0, 22) + '...' : label) + ' (' + data.datasets[0].data[i] + ')',
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    strokeStyle: data.datasets[0].backgroundColor[i],
                                    lineWidth: 0,
                                    hidden: false,
                                    index: i,
                                    fontColor: textColor 
                                }));
                            }
                        }
                    }
                }
            }
        });

        // Dynamic theme switching: Re-render if dark mode is toggled via our manager
        window.addEventListener('dark-mode-toggled', () => {
            console.log("Dark mode toggle detected - Updating Charts...");
            this.initCharts(months, applicantsTrends, completionTrends, completionRateTrends, schoolLabels, schoolTotals);
        }, { once: true });
    },
    exportThemedPDF() {
        const completionBase64 = document.getElementById('completionChart').toDataURL('image/png');
        const schoolBase64 = document.getElementById('schoolChart').toDataURL('image/png');
        document.getElementById('completionChartInput').value = completionBase64;
        document.getElementById('schoolChartInput').value = schoolBase64;
        document.getElementById('pdfForm').submit();
    }
}));

/**
 * Intern Attendance Manager
 */
Alpine.data('attendanceManager', (checkStatusRoute, isClockedIn, isCompleted, initialToken, initialLink, canScan) => ({
    isClockedIn: isClockedIn,
    isCompleted: isCompleted,
    currentToken: initialToken,
    verificationLink: initialLink,
    canScan: canScan,
    showLink: false,
    loading: false,
    init() {

        if (!this.isCompleted) {
            setInterval(() => this.checkAttendanceStatus(checkStatusRoute), 3000);
        }
    },
    checkAttendanceStatus(route) {
        const fetchRoute = `${route}?current_token=${this.currentToken}`;

        fetch(fetchRoute)
            .then(response => response.json())
            .then(data => {
                if (data.is_clocked_in !== this.isClockedIn || data.is_completed !== this.isCompleted || data.can_scan !== this.canScan) {
                    window.location.reload();
                } else if (data.token && data.token !== this.currentToken && data.qr_code_html) {
                    const qrContainer = document.getElementById('qr-code-container');
                    if (qrContainer) {
                        qrContainer.innerHTML = data.qr_code_html;
                        this.currentToken = data.token;
                        if (data.verification_link) {
                            this.verificationLink = data.verification_link;
                        }
                    }
                }
            })
            .catch(error => console.error('Error checking attendance status:', error));
    }
}));

/**
 * Intern Dashboard Manager
 */
Alpine.data('internDashboardManager', (userId) => ({
    loading: false,
    init() {

        if (window.Echo) {
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.task.assigned', (e) => {
                    if (window.showToast) {
                        window.showToast(`New Task: ${e.task.title}`, 'success');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                });
        }
    }
}));

/**
 * Admin Dashboard Manager
 */
Alpine.data('adminDashboardManager', () => ({
    loading: false,
    init() {}
}));

/**
 * Admin Security Monitor Manager
 */
Alpine.data('securityMonitorManager', (recentUserLogs = {}) => ({
    loading: false,
    showModal: false,
    evaluatingUser: null,
    evaluatingLog: null,
    evaluatingDisplayName: '',
    selectedAction: 'safe',
    userLogsMap: recentUserLogs,

    init() {},

    openEvaluation(user, log, displayName, userRouteKey) {
        this.evaluatingUser = user;
        if (this.evaluatingUser) {
            this.evaluatingUser.route_key = userRouteKey;
        }
        this.evaluatingLog = log;
        this.evaluatingDisplayName = displayName;
        this.selectedAction = 'safe';
        this.showModal = true;
    },

    getSubmitUrl() {
        if (!this.evaluatingUser || !this.evaluatingLog) return '';
        
        let pathPart = 'mark-safe';
        if (this.selectedAction === 'compromised') {
            pathPart = 'mark-compromised';
        } else if (this.selectedAction === 'suspended') {
            pathPart = 'mark-suspended';
        }
        
        const userRouteKey = this.evaluatingUser.route_key || this.evaluatingUser.id;
        return `/admin/security/${pathPart}/${userRouteKey}/${this.evaluatingLog.log_id}`;
    }
}));

/**
 * Admin Offices Manager
 */
Alpine.data('officesManager', () => ({
    loading: false,
    init() {}
}));

Alpine.start();

// Session Check / BFcache Reload Logic
(function() {
    window.addEventListener('pageshow', function(event) {
        const perfEntries = window.performance.getEntriesByType("navigation");
        const isBackForward = perfEntries.length > 0 && perfEntries[0].type === "back_forward";
        
        if (event.persisted || isBackForward) {
            window.location.reload();
        }
    });
})();