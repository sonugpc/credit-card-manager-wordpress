function addArrayItem(containerId, inputName, placeholder) {
    const container = document.getElementById(containerId);
    const newItem = document.createElement('div');
    newItem.className = 'ccm-array-item';
    newItem.innerHTML = `
        <input type="text" name="${inputName}" value="" placeholder="${placeholder}" />
        <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)">Remove</button>
    `;
    container.insertBefore(newItem, container.lastElementChild);
}

function removeArrayItem(button) {
    const item = button.parentElement;
    const container = item.parentElement;
    if (container.querySelectorAll('.ccm-array-item').length > 1) {
        item.remove();
    }
}

function addFaqItem() {
    const container = document.getElementById('custom-faqs-field');
    const existingItems = container.querySelectorAll('.ccm-faq-item');
    const newIndex = existingItems.length;
    
    const newItem = document.createElement('div');
    newItem.className = 'ccm-faq-item';
    newItem.innerHTML = `
        <div class="ccm-field">
            <label>Question</label>
            <input type="text" name="custom_faqs[${newIndex}][question]" value="" placeholder="Enter FAQ question" />
        </div>
        <div class="ccm-field">
            <label>Answer</label>
            <textarea name="custom_faqs[${newIndex}][answer]" rows="3" placeholder="Enter FAQ answer"></textarea>
        </div>
        <button type="button" class="ccm-remove-item" onclick="removeFaqItem(this)">Remove FAQ</button>
    `;
    
    const addButton = container.querySelector('.ccm-add-item');
    container.insertBefore(newItem, addButton);
}

function removeFaqItem(button) {
    const item = button.parentElement;
    const container = item.parentElement;
    if (container.querySelectorAll('.ccm-faq-item').length > 1) {
        item.remove();
        // Reindex the remaining items
        const items = container.querySelectorAll('.ccm-faq-item');
        items.forEach((item, index) => {
            const questionInput = item.querySelector('input[type="text"]');
            const answerTextarea = item.querySelector('textarea');
            if (questionInput) questionInput.name = `custom_faqs[${index}][question]`;
            if (answerTextarea) answerTextarea.name = `custom_faqs[${index}][answer]`;
        });
    }
}

// JSON Import/Export Functions
function showStatus(message, type) {
    const status = document.getElementById('ccm-json-status');
    status.textContent = message;
    status.className = 'ccm-json-status ' + type;
    status.style.display = 'block';
    setTimeout(() => {
        status.style.display = 'none';
    }, 5000);
}

function setFieldValue(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (field) {
        if (field.type === 'checkbox') {
            field.checked = Boolean(value);
        } else {
            field.value = value;
        }
    }
}

function setArrayField(containerId, values, inputName) {
    const container = document.getElementById(containerId);
    if (!container || !Array.isArray(values)) return;
    
    // Clear existing items except the last one (template)
    const existingItems = container.querySelectorAll('.ccm-array-item');
    existingItems.forEach((item, index) => {
        if (index < existingItems.length - 1) {
            item.remove();
        }
    });
    
    // Add new items
    values.forEach((value, index) => {
        if (index === 0) {
            // Use the existing first item
            const firstInput = container.querySelector('.ccm-array-item input');
            if (firstInput) firstInput.value = value;
        } else {
            // Add new items
            addArrayItem(containerId, inputName, 'Enter value');
            const newInput = container.querySelectorAll('.ccm-array-item input')[index];
            if (newInput) newInput.value = value;
        }
    });
}

function setFaqField(faqs) {
    const container = document.getElementById('custom-faqs-field');
    if (!container || !Array.isArray(faqs)) return;
    
    // Clear existing FAQ items
    const existingItems = container.querySelectorAll('.ccm-faq-item');
    existingItems.forEach(item => item.remove());
    
    // Add FAQ items
    faqs.forEach((faq, index) => {
        addFaqItem();
        const faqItems = container.querySelectorAll('.ccm-faq-item');
        const currentItem = faqItems[index];
        if (currentItem) {
            const questionInput = currentItem.querySelector('input[type="text"]');
            const answerTextarea = currentItem.querySelector('textarea');
            if (questionInput) questionInput.value = faq.question || '';
            if (answerTextarea) answerTextarea.value = faq.answer || '';
        }
    });
    
    // Add one empty item if no FAQs
    if (faqs.length === 0) {
        addFaqItem();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if(document.getElementById('ccm-import-json')) {
        document.getElementById('ccm-import-json').addEventListener('click', function() {
            try {
                const jsonText = document.getElementById('ccm-json-input').value.trim();
                if (!jsonText) {
                    showStatus('Please paste JSON data first.', 'error');
                    return;
                }
                
                const data = JSON.parse(jsonText);
                let importedFields = 0;
                
                // Import basic fields
                if (data.basic) {
                    if (data.basic.rating !== undefined) { setFieldValue('rating', data.basic.rating); importedFields++; }
                    if (data.basic.review_count !== undefined) { setFieldValue('review_count', data.basic.review_count); importedFields++; }
                    if (data.basic.featured !== undefined) { setFieldValue('featured', data.basic.featured); importedFields++; }
                    if (data.basic.trending !== undefined) { setFieldValue('trending', data.basic.trending); importedFields++; }
                }
                
                // Import fees
                if (data.fees) {
                    if (data.fees.annual_fee !== undefined) { setFieldValue('annual_fee', data.fees.annual_fee); importedFields++; }
                    if (data.fees.joining_fee !== undefined) { setFieldValue('joining_fee', data.fees.joining_fee); importedFields++; }
                    if (data.fees.welcome_bonus !== undefined) { setFieldValue('welcome_bonus', data.fees.welcome_bonus); importedFields++; }
                    if (data.fees.welcome_bonus_points !== undefined) { setFieldValue('welcome_bonus_points', data.fees.welcome_bonus_points); importedFields++; }
                    if (data.fees.welcome_bonus_type !== undefined) { setFieldValue('welcome_bonus_type', data.fees.welcome_bonus_type); importedFields++; }
                    if (data.fees.cashback_rate !== undefined) { setFieldValue('cashback_rate', data.fees.cashback_rate); importedFields++; }
                }
                
                // Import rewards
                if (data.rewards) {
                    if (data.rewards.reward_type !== undefined) { setFieldValue('reward_type', data.rewards.reward_type); importedFields++; }
                    if (data.rewards.reward_conversion_rate !== undefined) { setFieldValue('reward_conversion_rate', data.rewards.reward_conversion_rate); importedFields++; }
                    if (data.rewards.reward_conversion_value !== undefined) { setFieldValue('reward_conversion_value', data.rewards.reward_conversion_value); importedFields++; }
                }
                
                // Import eligibility
                if (data.eligibility) {
                    if (data.eligibility.credit_limit !== undefined) { setFieldValue('credit_limit', data.eligibility.credit_limit); importedFields++; }
                    if (data.eligibility.interest_rate !== undefined) { setFieldValue('interest_rate', data.eligibility.interest_rate); importedFields++; }
                    if (data.eligibility.processing_time !== undefined) { setFieldValue('processing_time', data.eligibility.processing_time); importedFields++; }
                    if (data.eligibility.min_income !== undefined) { setFieldValue('min_income', data.eligibility.min_income); importedFields++; }
                    if (data.eligibility.min_age !== undefined) { setFieldValue('min_age', data.eligibility.min_age); importedFields++; }
                    if (data.eligibility.max_age !== undefined) { setFieldValue('max_age', data.eligibility.max_age); importedFields++; }
                }
                
                // Import lists
                if (data.lists) {
                    if (data.lists.pros) { setArrayField('pros-field', data.lists.pros, 'pros[]'); importedFields++; }
                    if (data.lists.cons) { setArrayField('cons-field', data.lists.cons, 'cons[]'); importedFields++; }
                    if (data.lists.best_for) { setArrayField('best_for-field', data.lists.best_for, 'best_for[]'); importedFields++; }
                    if (data.lists.documents) { setArrayField('documents-field', data.lists.documents, 'documents[]'); importedFields++; }
                }
                
                // Import custom FAQs
                if (data.custom_faqs) {
                    setFaqField(data.custom_faqs);
                    importedFields++;
                }
                
                showStatus(`✅ Successfully imported ${importedFields} field groups from JSON!`, 'success');
                
            } catch (error) {
                showStatus('❌ Invalid JSON format. Please check your data and try again.', 'error');
                console.error('JSON Import Error:', error);
            }
        });
    }

    if(document.getElementById('ccm-export-json')) {
        document.getElementById('ccm-export-json').addEventListener('click', function() {
            try {
                const data = {
                    basic: {
                        rating: parseFloat(document.getElementById('rating')?.value || 0),
                        review_count: parseInt(document.getElementById('review_count')?.value || 0),
                        featured: document.getElementById('featured')?.checked || false,
                        trending: document.getElementById('trending')?.checked || false
                    },
                    fees: {
                        annual_fee: parseInt(document.getElementById('annual_fee')?.value || 0),
                        joining_fee: parseInt(document.getElementById('joining_fee')?.value || 0),
                        welcome_bonus: document.getElementById('welcome_bonus')?.value || '',
                        welcome_bonus_points: parseInt(document.getElementById('welcome_bonus_points')?.value || 0),
                        welcome_bonus_type: document.getElementById('welcome_bonus_type')?.value || 'points',
                        cashback_rate: document.getElementById('cashback_rate')?.value || ''
                    },
                    rewards: {
                        reward_type: document.getElementById('reward_type')?.value || '',
                        reward_conversion_rate: document.getElementById('reward_conversion_rate')?.value || '',
                        reward_conversion_value: parseFloat(document.getElementById('reward_conversion_value')?.value || 0)
                    },
                    eligibility: {
                        credit_limit: document.getElementById('credit_limit')?.value || '',
                        interest_rate: document.getElementById('interest_rate')?.value || '',
                        processing_time: document.getElementById('processing_time')?.value || '',
                        min_income: document.getElementById('min_income')?.value || '',
                        min_age: document.getElementById('min_age')?.value || '',
                        max_age: document.getElementById('max_age')?.value || ''
                    },
                    lists: {
                        pros: Array.from(document.querySelectorAll('input[name="pros[]"]')).map(input => input.value).filter(v => v),
                        cons: Array.from(document.querySelectorAll('input[name="cons[]"]')).map(input => input.value).filter(v => v),
                        best_for: Array.from(document.querySelectorAll('input[name="best_for[]"]')).map(input => input.value).filter(v => v),
                        documents: Array.from(document.querySelectorAll('input[name="documents[]"]')).map(input => input.value).filter(v => v)
                    },
                    custom_faqs: Array.from(document.querySelectorAll('.ccm-faq-item')).map(item => {
                        const question = item.querySelector('input[type="text"]')?.value || '';
                        const answer = item.querySelector('textarea')?.value || '';
                        return { question, answer };
                    }).filter(faq => faq.question || faq.answer)
                };
                
                document.getElementById('ccm-json-input').value = JSON.stringify(data, null, 2);
                showStatus('✅ Current form data exported to JSON textarea!', 'success');
                
            } catch (error) {
                showStatus('❌ Error exporting data. Please try again.', 'error');
                console.error('JSON Export Error:', error);
            }
        });
    }

    if(document.getElementById('ccm-clear-json')) {
        document.getElementById('ccm-clear-json').addEventListener('click', function() {
            document.getElementById('ccm-json-input').value = '';
            showStatus('🗑️ JSON textarea cleared.', 'success');
        });
    }
});
