<?php
Yii::import('application.commands.*');
use function print_r as printR;

/**
 * A test area for executing experimental PHP code inside of a Yii run environment.
 *
 * @package application.commands
 */
class FixOldQuotesCommand extends CConsoleCommand {
	public function run($args) {
        $quotes = Quote::model()->findAll();
        $quoteIds = array();

        // Fill array with all quote IDs that will be
        // used to find their respective line items 
        foreach($quotes as $index => $model) {
           $quoteIds[] = $model->id;
        }
        
        echo "Fixing quotes\n";
        $count = 1;
        foreach($quoteIds as $modelId) {
            // Find all line items related to the current quote
            $quoteProducts = QuoteProduct::model()->findAllByAttributes(array(
                'quoteId' => $modelId
            ), array(
                'order' => 'lineNumber ASC'
            ));

            // Since re-numbering line items is not an issue as long as 
            // the order of line items is the same, we will reset to 1.
            // This is incase we run into a line item with a description that
            // is not already a comment line.
            $quoteSubtotal = 0;
            $lineNumber = 1;
            $tempTotal = 0.00;
            foreach($quoteProducts as $lineItem) {
                $lineItem->lineNumber = $lineNumber++;

                // Check if the current line item is an actual product and not
                // an adjustment or comment.
                if(!in_array($lineItem->name, array('x2adjustment', 'x2comment'))) {
                    if($lineItem->adjustment != 0.00) {
                        $dupeCheck = QuoteProduct::model()->findByAttributes(array(
                            'name' => 'x2adjustment',
                            'quoteId' => $modelId,
                            'adjustment' => $lineItem->adjustment,
                            'adjustmentType' => $lineItem->adjustmentType,
                        ));
                        
                        // If there isn't already an adjustment line item, we will make one using
                        // the line item's adjustment information.
                        if(is_null($dupeCheck)) {
                            $adjustment = new QuoteProduct;
                            $adjustment->name = 'x2adjustment';
                            $adjustment->quoteId = $lineItem->quoteId;
                            $adjustment->quantity = 1;
                            $adjustment->lineNumber = $lineNumber++;
                            $adjustment->adjustmentType = $lineItem->adjustmentType;
                            $adjustment->adjustment = $lineItem->adjustment;

                            // Alter the original line item to not have an adjustment or adjustment type
                            // and re-calculate it's total
                            $lineItem->adjustment = 0.00;
                            $lineItem->adjustmentType = 'linear';
                            $lineItem->total = $lineItem->price * $lineItem->quantity;
                            if($adjustment->adjustmentType == 'percent')
                                $adjustment->total = $lineItem->total * ($adjustment->adjustment / 100);
                            else if($adjustment->adjustmentType == 'linear')
                                $adjustment->total = $adjustment->adjustment;
                            $adjustment->createDate = $adjustment->lastUpdated = time();
                            $adjustment->save();
    
                            $quoteSubtotal += $adjustment->total;
                        }
                    }                    
   
                    if(!is_null($lineItem->description) && !empty($lineItem->description)) {
                        // Check to see if the line item's description already exists as a comment line
                        $duplicateDescription = QuoteProduct::model()->findByAttributes(array(
                            'name' => 'x2comment', 
                            'quoteId' => $lineItem->quoteId, 
                            'description' => $lineItem->description,
                        ));

                        // If there isn't already a comment line, we will make one using
                        // the line item's description.
                        if(is_null($duplicateDescription)) {
                            $comment = new QuoteProduct;
                            $comment->name = 'x2comment';
                            $comment->quoteId = $lineItem->quoteId;
                            $comment->lineNumber = $lineNumber++;
                            $comment->description = $lineItem->description;
                            $lineItem->description = null;
                            $comment->createDate = $comment->lastUpdated = time();
                            $comment->save();
                        }
                    }

                    // Re-calculate the line item's total if it's not correct
                    // and set tempTotal equal to it so it can be used for
                    // setting an adjustment line item total
                    if($lineItem->total != ($lineItem->price * $lineItem->quantity)) {
                        $lineItem->total = $lineItem->price * $lineItem->quantity;
                        $tempTotal = $lineItem->total;
                    }
                }
                
                // Set an adjustment line item's total
                if($lineItem->name == 'x2adjustment')
                    if($lineItem->adjustmentType == 'percent')
                        $lineItem->total = $tempTotal * ($lineItem->adjustment / 100);
                    else if($lineItem->adjustmentType == 'linear')
                        $lineItem->total = $lineItem->adjustment;

                $lineItem->lastUpdated = time();
                $lineItem->save();
                $quoteSubtotal += $lineItem->total;
            }

            $quote = Quote::model()->findByPk($modelId);
            $quote->subtotal = $quoteSubtotal;
            $quote->total = $quote->subtotal + (floor($quote->subtotal * $quote->tax) / 100);
            if($quote->save())
                echo "Finished quote with ID: " . $modelId . ". #" . $count++ . " of " . count($quoteIds) . "\n";
            else
                echo "Failed to save quote: " . $modelId . "\n";
        }

        echo "Goodbye! :)\n";
        return;
    }
}

?>
