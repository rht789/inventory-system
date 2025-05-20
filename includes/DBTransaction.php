<?php
/**
 * DBTransaction - A helper class for database transactions
 * 
 * This class simplifies working with PDO transactions by providing 
 * a consistent interface and automatic rollback on errors.
 */
class DBTransaction {
    private $pdo;
    private $active = false;
    private $errorMessage = null;
    
    /**
     * Constructor
     * 
     * @param PDO $pdo The PDO instance to use for transactions
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Begin a database transaction
     * 
     * @return bool Whether the transaction was started successfully
     */
    public function begin() {
        if ($this->active) {
            $this->errorMessage = "Transaction already active";
            return false;
        }
        
        try {
            $this->active = $this->pdo->beginTransaction();
            return $this->active;
        } catch (PDOException $e) {
            $this->errorMessage = "Failed to start transaction: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Commit the transaction
     * 
     * @return bool Whether the commit was successful
     */
    public function commit() {
        if (!$this->active) {
            $this->errorMessage = "No active transaction to commit";
            return false;
        }
        
        try {
            $this->pdo->commit();
            $this->active = false;
            return true;
        } catch (PDOException $e) {
            $this->errorMessage = "Failed to commit transaction: " . $e->getMessage();
            $this->pdo->rollBack();
            $this->active = false;
            return false;
        }
    }
    
    /**
     * Rollback the transaction
     * 
     * @return bool Whether the rollback was successful
     */
    public function rollback() {
        if (!$this->active) {
            $this->errorMessage = "No active transaction to roll back";
            return false;
        }
        
        try {
            $this->pdo->rollBack();
            $this->active = false;
            return true;
        } catch (PDOException $e) {
            $this->errorMessage = "Failed to roll back transaction: " . $e->getMessage();
            $this->active = false;
            return false;
        }
    }
    
    /**
     * Execute a callback within a transaction
     * 
     * This method handles beginning, committing, and rolling back a transaction
     * automatically based on whether the callback succeeds or throws an exception.
     * 
     * @param callable $callback The function to execute within the transaction
     * @return mixed The return value of the callback, or false on failure
     */
    public function execute($callback) {
        if (!$this->begin()) {
            return false;
        }
        
        try {
            $result = $callback($this->pdo);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->rollback();
            return false;
        }
    }
    
    /**
     * Check if there's an active transaction
     * 
     * @return bool Whether a transaction is active
     */
    public function isActive() {
        return $this->active;
    }
    
    /**
     * Get the last error message
     * 
     * @return string|null The last error message, or null if no error
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }
    
    /**
     * Create a log entry for transaction operations
     * 
     * @param string $operation The operation being performed (begin, commit, rollback)
     * @param string $status The status of the operation (success, failure)
     * @param string $message Additional information about the operation
     */
    private function logTransaction($operation, $status, $message = '') {
        $logEntry = date('[Y-m-d H:i:s]') . " Transaction $operation: $status";
        if ($message) {
            $logEntry .= " - $message";
        }
        
        // Log to a file (you can customize this to log to database or other destinations)
        error_log($logEntry . PHP_EOL, 3, __DIR__ . '/../logs/transactions.log');
    }
} 