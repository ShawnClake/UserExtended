# Timezonable Trait

## Usage
1. Add this trait to a model:

        use Clake\UserExtended\Traits\Timezonable;
    
2. Add a protected data member to your model:
        
        protected $timezonable = [
            'created_at',
            'updated_at'
        ];
            
    This contains model attributes to convert to a users timezone
    
3. The trait will automatically convert timezones when using the model attributes specified. Defaults to UTC.