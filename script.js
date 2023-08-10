let contentItems = [];

// Function to add content item
function addContent() {
  const contentInput = document.getElementById('contentInput');
  const content = contentInput.value.trim();

  if (content !== '') {
    contentItems.push(content);
    contentInput.value = '';
    updateContentList();
    saveToLocalStorage();
  }
}

// Function to delete content item
function deleteContent(index) {
  contentItems.splice(index, 1);
  updateContentList();
  saveToLocalStorage();
}

// Function to edit content item
function editContent(index) {
  const newContent = prompt('Edit content:', contentItems[index]);
  if (newContent !== null && newContent.trim() !== '') {
    contentItems[index] = newContent.trim();
    updateContentList();
    saveToLocalStorage();
  }
}

// Function to update the content list
function updateContentList() {
  const contentList = document.getElementById('contentList');
  contentList.innerHTML = '';

  contentItems.forEach((content, index) => {
    const li = document.createElement('li');
    li.setAttribute('draggable', 'true'); // Set draggable attribute
    li.dataset.index = index; // Set index as a data attribute
    li.innerHTML = `
      <span>${content}</span>
      <div class="action-buttons">
        <button onclick="editContent(${index})">Edit</button>
        <button onclick="deleteContent(${index})">Delete</button>
      </div>
    `;
    contentList.appendChild(li);
  });
}

// Function to save content items to local storage
function saveToLocalStorage() {
  localStorage.setItem('contentItems', JSON.stringify(contentItems));
}

// Function to load content items from local storage
function loadFromLocalStorage() {
  const storedContent = localStorage.getItem('contentItems');
  if (storedContent) {
    contentItems = JSON.parse(storedContent);
    updateContentList();
  }
}

// Event listener to handle drag and drop
let draggedItemIndex;

function handleDragStart(event) {
  draggedItemIndex = parseInt(event.target.dataset.index);
  event.dataTransfer.setData('text/plain', event.target.dataset.index);
}

function handleDragOver(event) {
  event.preventDefault();
}

function handleDrop(event) {
  const targetItemIndex = parseInt(event.dataTransfer.getData('text'));
  const [draggedItem] = contentItems.splice(draggedItemIndex, 1);
  contentItems.splice(targetItemIndex, 0, draggedItem);
  updateContentList();
  saveToLocalStorage();
}

// Load content from local storage on page load
loadFromLocalStorage();

// Add event listeners for drag and drop
const contentList = document.getElementById('contentList');
contentList.addEventListener('dragstart', handleDragStart);
contentList.addEventListener('dragover', handleDragOver);
contentList.addEventListener('drop', handleDrop);
