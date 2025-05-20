document.addEventListener('DOMContentLoaded', function() {
    loadTasks();
});

function showError(message) {
    const errorElement = document.getElementById('errorMessage');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 3000);
}

async function loadTasks(filter = 'all') {
    try {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.filter-btn[onclick="filterTasks('${filter}')"]`).classList.add('active');
        
        const response = await fetch(`api/read.php?filter=${filter}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const tasks = await response.json();
        const taskList = document.getElementById('taskList');
        taskList.innerHTML = '';
        
        if (tasks.length === 0) {
            taskList.innerHTML = '<li class="no-tasks">No tasks found</li>';
            document.getElementById('taskCount').textContent = '0 tasks';
            return;
        }
        
        tasks.forEach(task => {
            const taskItem = document.createElement('li');
            taskItem.className = `task-item ${task.completed ? 'completed' : ''}`;
            taskItem.innerHTML = `
                <div class="task-content">
                    <div class="task-title">${escapeHtml(task.title)}</div>
                    ${task.description ? `<div class="task-desc">${escapeHtml(task.description)}</div>` : ''}
                </div>
                <div class="task-actions">
                    <button class="complete-btn" onclick="toggleComplete(${task.id}, ${task.completed})">
                        ${task.completed ? 'Undo' : 'Complete'}
                    </button>
                    <button class="delete-btn" onclick="deleteTask(${task.id})">Delete</button>
                </div>
            `;
            taskList.appendChild(taskItem);
        });
        
        // Update task count
        const activeCount = tasks.filter(task => !task.completed).length;
        const totalCount = tasks.length;
        document.getElementById('taskCount').textContent = 
            `${activeCount} ${activeCount === 1 ? 'task' : 'tasks'} remaining (${totalCount} total)`;
            
    } catch (error) {
        console.error('Error loading tasks:', error);
        showError('Failed to load tasks. Please try again.');
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function addTask() {
    const title = document.getElementById('taskTitle').value.trim();
    const description = document.getElementById('taskDesc').value.trim();
    
    if (!title) {
        showError('Please enter a task title');
        return;
    }
    
    try {
        const response = await fetch('api/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: title,
                description: description
            })
        });
        
        const data = await response.json();
        
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to add task');
        }
        
        document.getElementById('taskTitle').value = '';
        document.getElementById('taskDesc').value = '';
        loadTasks();
    } catch (error) {
        console.error('Error adding task:', error);
        showError(error.message || 'Failed to add task. Please try again.');
    }
}

async function toggleComplete(id, isCompleted) {
    try {
        const response = await fetch('api/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                completed: !isCompleted
            })
        });
        
        const data = await response.json();
        
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to update task');
        }
        
        loadTasks();
    } catch (error) {
        console.error('Error updating task:', error);
        showError('Failed to update task status. Please try again.');
    }
}

async function deleteTask(id) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id
            })
        });
        
        const data = await response.json();
        
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to delete task');
        }
        
        loadTasks();
    } catch (error) {
        console.error('Error deleting task:', error);
        showError('Failed to delete task. Please try again.');
    }
}

async function clearCompleted() {
    if (!confirm('Are you sure you want to clear all completed tasks?')) {
        return;
    }
    
    try {
        // First get all completed tasks
        const response = await fetch('api/read.php?filter=completed');
        const completedTasks = await response.json();
        
        // Delete each completed task
        for (const task of completedTasks) {
            await fetch('api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: task.id
                })
            });
        }
        
        loadTasks();
    } catch (error) {
        console.error('Error clearing completed tasks:', error);
        showError('Failed to clear completed tasks. Please try again.');
    }
}

function filterTasks(filter) {
    loadTasks(filter);
}