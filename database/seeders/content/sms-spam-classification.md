## The problem

SMS spam detection is a classic binary text-classification problem: given a short message, decide whether it is spam or a legitimate ("ham") message. SMS text is short, informal, and full of abbreviations, so the pipeline has to turn noisy text into features a model can learn from.

[[architecture]]

## Approach

### Text preprocessing

Messages were cleaned and normalized before feature extraction, so that superficial differences in formatting wouldn't become features in their own right.

### Feature extraction with TF-IDF

TF-IDF (term frequency–inverse document frequency) turns each message into a sparse vector that weights words by how characteristic they are of that message relative to the whole collection. Words that appear everywhere count for little; distinctive ones — the kind common in promotional spam — count for more.

### Training a Support Vector Machine

A Support Vector Machine learns a decision boundary that separates the two classes with the widest possible margin. SVMs work well with high-dimensional, sparse features like TF-IDF vectors.

### Hyperparameter tuning with GridSearchCV

GridSearchCV evaluated combinations of model hyperparameters with cross-validation and selected the best-performing configuration.

## Results

The final model achieved a **reported 98% classification accuracy** on the project's evaluation data.

## Limitations

- **Accuracy isn't the whole story.** Spam datasets are usually imbalanced, so a model can score highly on accuracy while still missing spam. Precision, recall, and a confusion matrix give a fuller picture — they're what I'd report alongside accuracy in a follow-up.
- **Results are dataset-dependent.** Performance was measured on the project's dataset. Spam patterns change over time and across regions, so results won't automatically transfer to other message sources.
- **A learning project, not a live filter.** This was built as an internship project, not as a production filtering service.
