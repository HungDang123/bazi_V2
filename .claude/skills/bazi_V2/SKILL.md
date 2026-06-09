```markdown
# bazi_V2 Development Patterns

> Auto-generated skill from repository analysis

## Overview
This skill teaches the core development conventions and workflows used in the `bazi_V2` TypeScript codebase. It covers file naming, import/export styles, commit patterns, and testing strategies. By following these guidelines, contributors can ensure code consistency and maintainability throughout the project.

## Coding Conventions

### File Naming
- Use **snake_case** for all file names.

  **Example:**
  ```
  user_service.ts
  data_processor.test.ts
  ```

### Import Style
- Use **relative imports** for referencing modules within the project.

  **Example:**
  ```typescript
  import { calculateScore } from './score_utils';
  ```

### Export Style
- Use **named exports** for all module exports.

  **Example:**
  ```typescript
  // In user_service.ts
  export function createUser() { ... }
  export function deleteUser() { ... }
  ```

  ```typescript
  // In another file
  import { createUser, deleteUser } from './user_service';
  ```

### Commit Patterns
- Commit messages are **freeform**, sometimes using short prefixes.
- Average commit message length: **11 characters**.

  **Example:**
  ```
  fix bug
  add tests
  update logic
  ```

## Workflows

### Adding a New Feature
**Trigger:** When implementing a new feature in the codebase  
**Command:** `/add-feature`

1. Create a new file using snake_case naming.
2. Implement the feature using TypeScript.
3. Use relative imports to include any dependencies.
4. Export functions or constants using named exports.
5. Write a corresponding test file named `feature_name.test.ts`.
6. Commit changes with a clear, concise message.

### Writing and Running Tests
**Trigger:** When validating code functionality  
**Command:** `/run-tests`

1. Create a test file with the pattern `*.test.ts` (e.g., `user_service.test.ts`).
2. Write test cases for your functions or modules.
3. Use the project's test runner (framework unknown; consult project docs or package.json).
4. Run the tests to ensure all cases pass.

### Refactoring Code
**Trigger:** When improving or restructuring existing code  
**Command:** `/refactor`

1. Identify the code to refactor.
2. Update file names to use snake_case if necessary.
3. Ensure all imports remain relative and exports are named.
4. Update or add tests as needed.
5. Commit with a brief message describing the refactor.

## Testing Patterns

- Test files follow the pattern: `*.test.ts`
- The testing framework is **unknown**; check the project's documentation or configuration for details.
- Place test files alongside the modules they test or in a dedicated test directory.

  **Example:**
  ```
  user_service.ts
  user_service.test.ts
  ```

- Tests should cover all exported functions and edge cases.

## Commands
| Command        | Purpose                                   |
|----------------|-------------------------------------------|
| /add-feature   | Scaffold and implement a new feature      |
| /run-tests     | Run all test files matching `*.test.ts`   |
| /refactor      | Refactor code following conventions       |
```