# Searchable Trait

## Usage
1. Add this trait to a model:

        use Clake\UserExtended\Traits\Searchable;
    
2. Add a protected data member to your model:
        
        protected $searchable = [
            'email',
            'name',
            'surname',
            'username'
        ];
            
    This contains model attributes to search
    
3. Utilize the traits search function:

        $results = new Model();

        return $results->search($phrase);