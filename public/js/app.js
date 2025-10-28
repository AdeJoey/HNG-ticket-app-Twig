// Ticket Management JavaScript
class TicketManager {
    constructor() {
        this.tickets = this.loadTickets();
        this.editingTicket = null;
        this.init();
    }

    init() {
        this.renderTickets();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const form = document.getElementById('ticketForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (this.editingTicket) {
                    this.updateTicket();
                } else {
                    this.addTicket();
                }
            });
        }
    }

    addTicket() {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const status = document.getElementById('status').value;

        if (!title) return;

        const ticket = {
            id: Date.now(),
            title,
            description,
            status
        };

        this.tickets.push(ticket);
        this.saveTickets();
        this.renderTickets();
        this.resetForm();
        this.showFeedback('✅ Ticket added successfully!');
    }

    deleteTicket(id) {
        if (!confirm('Are you sure you want to delete this ticket?')) return;
        
        this.tickets = this.tickets.filter(t => t.id !== id);
        this.saveTickets();
        this.renderTickets();
        this.showFeedback('🗑️ Ticket deleted.');
    }

    startEdit(id) {
        this.editingTicket = this.tickets.find(t => t.id === id);
        if (!this.editingTicket) return;

        document.getElementById('title').value = this.editingTicket.title;
        document.getElementById('description').value = this.editingTicket.description;
        document.getElementById('status').value = this.editingTicket.status;

        const submitBtn = document.querySelector('#ticketForm button[type="submit"]');
        submitBtn.textContent = 'Update Ticket';
        submitBtn.style.background = '#f59e0b';

        // Add cancel button
        let cancelBtn = document.getElementById('cancelEdit');
        if (!cancelBtn) {
            cancelBtn = document.createElement('button');
            cancelBtn.id = 'cancelEdit';
            cancelBtn.type = 'button';
            cancelBtn.textContent = 'Cancel';
            cancelBtn.className = 'cancel-btn';
            cancelBtn.onclick = () => this.cancelEdit();
            submitBtn.parentNode.appendChild(cancelBtn);
        }
    }

    updateTicket() {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const status = document.getElementById('status').value;

        const index = this.tickets.findIndex(t => t.id === this.editingTicket.id);
        if (index !== -1) {
            this.tickets[index] = {
                ...this.editingTicket,
                title,
                description,
                status
            };
        }

        this.saveTickets();
        this.renderTickets();
        this.cancelEdit();
        this.showFeedback('✏️ Ticket updated successfully!');
    }

    cancelEdit() {
        this.editingTicket = null;
        this.resetForm();
        
        const submitBtn = document.querySelector('#ticketForm button[type="submit"]');
        submitBtn.textContent = 'Add Ticket';
        submitBtn.style.background = '';

        const cancelBtn = document.getElementById('cancelEdit');
        if (cancelBtn) cancelBtn.remove();
    }

    resetForm() {
        document.getElementById('title').value = '';
        document.getElementById('description').value = '';
        document.getElementById('status').value = 'open';
    }

    renderTickets() {
        const container = document.getElementById('ticketList');
        if (!container) return;

        if (this.tickets.length === 0) {
            container.innerHTML = '<p class="empty-text">No tickets yet. Add one to get started!</p>';
            return;
        }

        container.innerHTML = this.tickets.map(ticket => this.renderTicketCard(ticket)).join('');

        // Attach event listeners (removed optional chaining for safety)
        this.tickets.forEach(ticket => {
            const editBtn = document.getElementById(`edit-${ticket.id}`);
            const deleteBtn = document.getElementById(`delete-${ticket.id}`);
            if (editBtn) editBtn.addEventListener('click', () => this.startEdit(ticket.id));
            if (deleteBtn) deleteBtn.addEventListener('click', () => this.deleteTicket(ticket.id));
        });
    }

    renderTicketCard(ticket) {
        const statusColors = {
            open: '#4CAF50',
            in_progress: '#FFC107',
            closed: '#9E9E9E'
        };

        return `
            <div class="ticket-card" style="border-left: 5px solid ${statusColors[ticket.status]}">
                <h3>${this.escapeHtml(ticket.title)}</h3>
                <p>${this.escapeHtml(ticket.description)}</p>
                <span class="status" style="color: ${statusColors[ticket.status]}">
                    ${ticket.status.replace('_', ' ')}
                </span>
                <div class="ticket-actions">
                    <button id="edit-${ticket.id}" class="edit-btn" aria-label="Edit ticket">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button id="delete-${ticket.id}" class="delete-btn" aria-label="Delete ticket">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showFeedback(message) {
        const feedback = document.getElementById('feedback');
        if (!feedback) return;

        feedback.textContent = message;
        feedback.style.display = 'inline-block';
        
        setTimeout(() => {
            feedback.style.display = 'none';
        }, 2500);
    }

    loadTickets() {
        const stored = localStorage.getItem('tickets');
        return stored ? JSON.parse(stored) : [];
    }

    saveTickets() {
        localStorage.setItem('tickets', JSON.stringify(this.tickets));
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ticketList')) {
        new TicketManager();
    }
});
