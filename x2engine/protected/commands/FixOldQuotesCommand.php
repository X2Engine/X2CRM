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
        
        printR("Fixing quotes\n");
        $count = 1;
        foreach($quoteIds as $modelId) {
            // Find all line items related to the current quote
            $quoteProducts = QuoteProduct::model()->findAllByAttributes(
                array('quoteId' => $modelId),
                array('order' => 'lineNumber ASC')
            );
            $quoteSubtotal = 0;

            // Since re-numbering line items is not an issue as long as 
            // the order of line items is the same, we will reset to 1.
            // This is incase we run into a line item with a description that
            // is not already a comment line.
            $lineNumber = 1;
            foreach($quoteProducts as $lineItem) {
                $lineItem->lineNumber = $lineNumber++;
                
                // Check if the current line item is an actual product and not
                // an adjustment or comment.
                if(!in_array($lineItem->name, array('x2adjustment', 'x2comment')) && !is_null($lineItem->description) && !empty($lineItem->description)) {
                    // Check to see if the line item's description already exists as a comment line
                    $duplicateDescription = QuoteProduct::model()->findByAttributes(
                        array('name' => 'x2comment', 'quoteId' => $lineItem->quoteId, 'description' => $lineItem->description)
                    );

                    // If there isn't already a comment line, we will make one using
                    // the line item's description.
                    if(is_null($duplicateDescription)) {
                        $comment = new QuoteProduct;
                        $comment->name = 'x2comment';
                        $comment->quoteId = $lineItem->quoteId;
                        $comment->lineNumber = $lineNumber++;
                        $comment->description = $lineItem->description;
                        $comment->save();
                    }
                }

                // If line item total is 0, then
                // calculate line total
                if($lineItem->total == 0.00)
                    $lineItem->total = $lineItem->price * $lineItem->quantity;

                $lineItem->save();
                $quoteSubtotal += $lineItem->total;
            }
            $quote = Quote::model()->findByPk($modelId);
            $quote->subtotal = $quoteSubtotal;
            $quote->total = $quote->subtotal + (floor($quote->subtotal * $quote->tax) / 100);
            if($quote->save())
                printR("Finished quote with ID: " . $modelId . ". #" . $count++ . " of " . count($quoteIds) . "\n");
            else
                printR("Failed to save quote: " . $modelId . "\n");
        }

        printR("Goodbye! :)\n");
        return;
    }
}

?>
