import './bootstrap';
import '../css/app.css';

// Initialize jQuery when DOM is ready
$(document).ready(function() {
    const API_BASE_URL = '/api/v1/profiles';
    let editingId = null;

    // CSRF token setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load all profiles
    function loadProfiles() {
        $.ajax({
            url: API_BASE_URL,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    displayProfiles(response.data);
                }
            },
            error: function(xhr) {
                showMessage('Error loading profiles', 'error');
                $('#profiles-list').html('<div class="text-center text-red-500 py-8">Error loading profiles</div>');
            }
        });
    }

    // Display profiles
    function displayProfiles(profiles) {
        if (profiles.length === 0) {
            $('#profiles-list').html('<div class="text-center text-gray-500 py-8">No profiles found. Create one above!</div>');
            return;
        }

        let html = '';
        profiles.forEach(function(profile) {
            html += `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition" data-id="${profile.id}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            ${profile.avatar ? `
                                <img src="${profile.avatar}" alt="${profile.name}" 
                                     class="w-16 h-16 rounded-full object-cover">
                            ` : `
                                <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-xl font-bold">
                                    ${profile.name.charAt(0).toUpperCase()}
                                </div>
                            `}
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-800">${profile.name}</h3>
                                <p class="text-gray-600 text-sm">${profile.email}</p>
                                ${profile.bio ? `<p class="text-gray-700 mt-2">${profile.bio}</p>` : ''}
                                <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-500">
                                    ${profile.phone ? `<span>📞 ${profile.phone}</span>` : ''}
                                    ${profile.location ? `<span>📍 ${profile.location}</span>` : ''}
                                    ${profile.website ? `<a href="${profile.website}" target="_blank" class="text-blue-600 hover:underline">🌐 Website</a>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="editProfile(${profile.id})" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                Edit
                            </button>
                            <button onclick="deleteProfile(${profile.id})" 
                                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#profiles-list').html(html);
    }

    // Show message
    function showMessage(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const messageHtml = `
            <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg mb-2 animate-fade-in">
                ${message}
            </div>
        `;
        $('#message-container').html(messageHtml);
        setTimeout(function() {
            $('#message-container').fadeOut(function() {
                $(this).html('').show();
            });
        }, 3000);
    }

    // Form submission
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#name').val(),
            email: $('#email').val(),
            bio: $('#bio').val(),
            phone: $('#phone').val(),
            location: $('#location').val(),
            website: $('#website').val(),
            avatar: $('#avatar').val()
        };

        const url = editingId ? `${API_BASE_URL}/${editingId}` : API_BASE_URL;
        const method = editingId ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    showMessage(editingId ? 'Profile updated successfully!' : 'Profile created successfully!', 'success');
                    resetForm();
                    loadProfiles();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMessage = 'An error occurred';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join(', ');
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showMessage(errorMessage, 'error');
            }
        });
    });

    // Reset form
    function resetForm() {
        $('#profile-form')[0].reset();
        $('#profile-id').val('');
        editingId = null;
        $('#form-title').text('Create Profile');
        $('#submit-btn').text('Create Profile');
        $('#cancel-btn').addClass('hidden');
    }

    // Edit profile
    window.editProfile = function(id) {
        $.ajax({
            url: `${API_BASE_URL}/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const profile = response.data;
                    editingId = id;
                    $('#profile-id').val(profile.id);
                    $('#name').val(profile.name);
                    $('#email').val(profile.email);
                    $('#bio').val(profile.bio || '');
                    $('#phone').val(profile.phone || '');
                    $('#location').val(profile.location || '');
                    $('#website').val(profile.website || '');
                    $('#avatar').val(profile.avatar || '');
                    $('#form-title').text('Edit Profile');
                    $('#submit-btn').text('Update Profile');
                    $('#cancel-btn').removeClass('hidden');
                    
                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $('#profile-form').offset().top - 20
                    }, 500);
                }
            },
            error: function() {
                showMessage('Error loading profile', 'error');
            }
        });
    };

    // Delete profile
    window.deleteProfile = function(id) {
        if (!confirm('Are you sure you want to delete this profile?')) {
            return;
        }

        $.ajax({
            url: `${API_BASE_URL}/${id}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    showMessage('Profile deleted successfully!', 'success');
                    loadProfiles();
                    if (editingId === id) {
                        resetForm();
                    }
                }
            },
            error: function() {
                showMessage('Error deleting profile', 'error');
            }
        });
    };

    // Cancel edit
    $('#cancel-btn').on('click', function() {
        resetForm();
    });

    // Initial load
    loadProfiles();
});
